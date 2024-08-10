<?php

namespace App\Filament\Resources\OutstandingResource\Pages;

use App\Filament\Resources\OutstandingResource;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Product;
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
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Tabs;
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
                            // old outstanding
                            // Select::make('outstanding_id')
                            //     ->label('Outstanding')
                            //     ->options(
                            //         Outstanding::where('outstandings.status', 0)
                            //             ->join('locations', 'outstandings.location_id', '=', 'locations.id')
                            //             ->select(
                            //                 DB::raw("CONCAT(locations.name, ' - ', outstandings.title) as title"),
                            //                 'outstandings.id'
                            //             )
                            //             ->pluck('title', 'id')
                            //     )
                            //     ->visible(fn ($get) => !$get('task'))
                            //     ->searchable()
                            //     ->columnSpan([
                            //         'md' => 2
                            //     ])
                            //     ->required(),
                            // Select::make('user_id')
                            //     ->label('Support')
                            //     ->visible(fn ($get) => !$get('task'))
                            //     ->searchable()
                            //     ->required()
                            //     ->options(function () {
                            //         $teams = Team::with('users')->get();
                            //         $options = [];

                            //         foreach ($teams as $team) {
                            //             $teamUsers = $team->users->pluck('name', 'id')->toArray();
                            //             $options[$team->name] = $teamUsers;
                            //         }

                            //         return $options;
                            //     }),
                            // DatePicker::make('date_visit')
                            //     ->label('Jadwal')
                            //     ->visible(fn ($get) => !$get('task'))
                            //     ->required()
                            //     ->default(Carbon::now())
                            //     ->native(false),
                            // new outstanding
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
                                // ->visible(fn ($get) => $get('task'))
                                ->searchable()
                                ->options(Location::where('team_id', $userTeam)->get()->pluck('name_alias', 'id'))
                                ->live()
                                ->required(),
                            TextInput::make('title')
                                ->label('Masalah')
                                ->visible(fn ($get) => $get('task'))
                                // ->columnSpan([
                                //     'md' => 2
                                // ])
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
                                // ->options(fn (Get $get): Collection => Outstanding::query()
                                //     ->where('location_id', $get('location_id'))
                                //     ->where('outstandings.status', 0)
                                //     ->whereNotExists(function ($query) {
                                //         $query->select(DB::raw(1))
                                //             ->from('reportings')
                                //             ->whereColumn('reportings.outstanding_id', 'outstandings.id')
                                //             ->whereNull('reportings.status');
                                //     })
                                //     ->pluck('title', 'id'))
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
                                ->visible(fn ($get) => $get('task')),
                            DatePicker::make('date_in')
                                ->label('Lapor')
                                ->visible(fn ($get) => $get('task'))
                                ->default(Carbon::now())
                                ->required()
                                ->native(false),
                            DatePicker::make('date_visit')
                                ->label('Jadwal')
                                // ->visible(fn ($get) => $get('task'))
                                ->default(Carbon::now())
                                ->required()
                                ->native(false),
                            Select::make('user_id')
                                ->label('Support')
                                // ->visible(fn ($get) => $get('task'))
                                ->multiple()
                                ->searchable()
                                ->required()
                                // ->columnSpan([
                                //     'md' => 2
                                // ])
                                ->options(function () {
                                    $teams = Team::with('users')->get();
                                    $options = [];

                                    foreach ($teams as $team) {
                                        $teamUsers = $team->users->pluck('name', 'id')->toArray();
                                        $options[$team->name] = $teamUsers;
                                    }
                                    return $options;
                                }),
                        ])
                        // ->columns(2)
                ])
                ->modalHeading('Buat Jadwal')
                // ->extraModalFooterActions(fn (Action $action): array => [
                //     $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                //         ->label('Kirim & buat lainnya'),
                // ])
                ->action(function (array $data, array $arguments): void {
                    if ($data['task']) {
                        // Create new outstanding
                        $product = Contract::where('location_id', $data['location_id'])->where('is_default', 1)->first();
                        // dd($product);
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
                        // Create new reporting
                        $reporting = Reporting::create([
                            'outstanding_id' => $outstanding->id,
                            // 'user_id' => $data['user_id'],
                            'date_visit' => $data['date_visit'],
                            'status' => null,
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
                            // Reporting::create([
                            //     'outstanding_id' => $check_id,
                            //     'user_id' => $data['user_id'],
                            //     'date_visit' => $data['date_visit'],
                            //     'status' => null,
                            // ]);
                            $reporting = Reporting::create([
                                'outstanding_id' => $check_id,
                                // 'user_id' => $data['user_id'],
                                'date_visit' => $data['date_visit'],
                                'status' => null,
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
