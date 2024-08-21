<?php

namespace App\Filament\Resources\OutstandingResource\Pages;

use App\Filament\Resources\OutstandingResource;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Reporting;
use App\Models\Team;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListOutstandings extends ListRecords
{
    protected static string $resource = OutstandingResource::class;

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole(['head', 'staff'])) {
            return parent::getTableQuery()
                ->join('locations', 'outstandings.location_id', '=', 'locations.id')
                ->join('teams', 'locations.team_id', '=', 'teams.id')
                ->where('teams.id', $user->team_id)
                ->select('outstandings.*');
        }

        return parent::getTableQuery();
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;

        return [
            Action::make('createAnother')
                ->label('Buat Jadwal')
                ->icon('heroicon-o-briefcase')
                ->form([
                    Section::make()
                        ->schema([
                            Toggle::make('task')
                                ->label('Existing <-> New')
                                ->live()
                                ->columnSpanFull(),
                            TextInput::make('number')
                                ->label('No. Tiket')
                                ->default('SP-' .Carbon::now()->format('ym').''.(random_int(100000, 999999)))
                                ->disabled()
                                ->visible(fn ($get) => $get('task'))
                                ->dehydrated()
                                ->required()
                                ->columnSpanFull()
                                ->maxLength(32)
                                ->unique(Outstanding::class, 'number', ignoreRecord: true),
                            Select::make('location_id')
                                ->label('Lokasi')
                                ->searchable()
                                ->options(Location::where('team_id', $userTeam)->get()->pluck('name_alias', 'id'))
                                ->live()
                                ->required(),
                            TextInput::make('title')
                                ->label('Masalah')
                                ->visible(fn ($get) => $get('task'))
                                ->required(),
                            CheckboxList::make('outstanding_id')
                                ->label('Masalah')
                                ->visible(fn ($get) => !$get('task'))
                                ->options(function (Get $get): array {
                                    $options = Outstanding::query()
                                        ->where('location_id', $get('location_id'))
                                        ->where('outstandings.status', 0)
                                        ->whereNotExists(function ($query) {
                                            $query->select(DB::raw(1))
                                                ->from('reportings')
                                                ->whereColumn('reportings.outstanding_id', 'outstandings.id')
                                                ->whereNull('reportings.status');
                                        })
                                        ->pluck('title', 'id')
                                        ->toArray();

                                    return !empty($options) ? $options : ['' => 'Tidak ada Outstanding'];
                                })
                                ->disableOptionWhen(fn (string $value): bool => $value === '')
                                ->required(),
                            Select::make('reporter')
                                ->label('Pelapor')
                                ->visible(fn ($get) => $get('task'))
                                ->options([
                                    'client' => 'Client',
                                    'preventif' => 'Preventif',
                                    'support' => 'Support',
                                ])
                                ->default('client')
                                ->required(),
                            Toggle::make('lpm')
                                ->label('Laporan Pertama')
                                ->inline(false)
                                ->visible(fn ($get) => $get('task')),
                            DatePicker::make('date_in')
                                ->label('Lapor')
                                ->visible(fn ($get) => $get('task'))
                                ->default(Carbon::now())
                                ->required()
                                ->native(false),
                            DatePicker::make('date_visit')
                                ->label('Jadwal')
                                ->default(Carbon::now())
                                ->required()
                                ->native(false),
                            Select::make('user_id')
                                ->label('Support')
                                ->multiple()
                                ->searchable()
                                ->required()
                                ->options(function () {
                                    $teams = Team::with('users')->get();
                                    $options = [];

                                    foreach ($teams as $team) {
                                        $teamUsers = $team->users->pluck('name', 'id')->toArray();
                                        $options[$team->name] = $teamUsers;
                                    }
                                    return $options;
                                }),
                        ])->columns(2)
                ])
                ->modalHeading('Buat Jadwal')
                ->action(function (array $data, array $arguments): void {
                    if ($data['task']) {
                        // Create new outstanding
                        $product = Contract::where('location_id', $data['location_id'])
                                            ->where('is_default', 1)
                                            ->first();

                        $outstanding = Outstanding::create([
                            'number' => $data['number'],
                            'location_id' => $data['location_id'],
                            'product_id' => $product->product_id ?? null,
                            'reporter' => $data['reporter'],
                            'lpm' => $data['lpm'],
                            'title' => $data['title'],
                            'date_in' => $data['date_in'],
                            'date_visit' => $data['date_visit'],
                        ]);

                        // Fetch email_to and email_cc from the pivot table
                        $location = Location::find($data['location_id']);
                        $emailTo = $location ? $location->customers()
                            ->wherePivot('is_to', true)
                            ->pluck('email')
                            ->toArray() : [];

                        $emailCc = $location ? $location->customers()
                            ->wherePivot('is_to', false)
                            ->pluck('email')
                            ->toArray() : [];

                        // Create new reporting
                        $reporting = Reporting::create([
                            'outstanding_id' => $outstanding->id,
                            'date_visit' => $data['date_visit'],
                            'status' => null,
                            'email_to' => $emailTo,
                            'email_cc' => $emailCc,
                        ]);
                        // Attach multiple users to the reporting
                        if (isset($data['user_id']) && is_array($data['user_id'])) {
                            $reporting->users()->attach($data['user_id']);
                        }

                        Notification::make()
                            ->title('Data berhasil dibuat')
                            ->success()
                            ->send();
                    } else {
                        // Create new reporting
                        foreach($data['outstanding_id'] as $check_id){
                            // Fetch the outstanding record
                            $outstanding = Outstanding::find($check_id);

                            // Fetch email_to and email_cc from the pivot table
                            $location = $outstanding->location;
                            $emailTo = $location ? $location->customers()
                                ->wherePivot('is_to', true)
                                ->pluck('email')
                                ->toArray() : [];

                            $emailCc = $location ? $location->customers()
                                ->wherePivot('is_to', false)
                                ->pluck('email')
                                ->toArray() : [];

                            $reporting = Reporting::create([
                                'outstanding_id' => $check_id,
                                'date_visit' => $data['date_visit'],
                                'status' => null,
                                'email_to' => $emailTo,
                                'email_cc' => $emailCc,
                            ]);
                            // Attach multiple users to the reporting
                            if (isset($data['user_id']) && is_array($data['user_id'])) {
                                $reporting->users()->attach($data['user_id']);
                            }
                        }

                        Notification::make()
                            ->title('Data berhasil dibuat')
                            ->success()
                            ->send();

                        // Notification::make()
                        //     ->title('Jadwal outstanding')
                        //     ->icon('heroicon-o-inbox-arrow-down')
                        //     ->body("<b>{$outstanding->location?->name} - {$outstanding?->title}</b>")
                        //     ->actions([
                        //         Action::make('Lihat')
                        //             ->url(SupportOutstandingResource::getUrl('edit', ['record' => $outstanding], panel: 'support')),
                        //     ])
                        //     ->sendToDatabase($sendUserSchedule);
                    }
                })
                ->stickyModalFooter()
                ->closeModalByEscaping(false)
                ->closeModalByClickingAway(false),
            Actions\CreateAction::make(),
        ];
    }
}
