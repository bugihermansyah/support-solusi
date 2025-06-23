<?php

namespace App\Filament\Resources\OutstandingResource\Pages;

use App\Filament\Resources\OutstandingResource;
use App\Jobs\ScheduleMailJob;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Reporting;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                ->label('Create Schedule')
                ->icon('heroicon-o-briefcase')
                ->form([
                    Section::make()
                        ->schema([
                            Group::make()
                                ->schema([
                                    TextInput::make('number')
                                        ->label('No. Tiket')
                                        ->inlineLabel()
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
                                        ->inlineLabel()
                                        ->searchable()
                                        ->options(Location::where('team_id', $userTeam)->get()->pluck('name_alias', 'id'))
                                        ->live()
                                        ->required()
                                        ->reactive() // Membuat lokasi reactive
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            $defaultProduct = Contract::where('location_id', $state)
                                                ->join('products', 'products.id', '=', 'contracts.product_id')
                                                ->where('is_default', 1)
                                                ->pluck('products.id')
                                                ->first();

                                            if ($defaultProduct) {
                                                $set('product_id', $defaultProduct);
                                            }
                                        }),
                                    TextInput::make('title')
                                        ->label('Masalah')
                                        ->inlineLabel()
                                        ->visible(fn ($get) => $get('task'))
                                        ->required(),
                                    Select::make('reporter')
                                        ->label('Pelapor')
                                        ->inlineLabel()
                                        ->visible(fn ($get) => $get('task'))
                                        ->options([
                                            'client' => 'Client',
                                            'preventif' => 'Preventif',
                                            'support' => 'Internal',
                                        ])
                                        ->default('client')
                                        ->live()
                                        ->required(),
                                    Toggle::make('lpm')
                                        ->label('Laporan Pertama')
                                        ->inlineLabel()
                                        ->visible(fn ($get) => $get('task') && $get('reporter') === 'client'),
                                    TextInput::make('reporter_name')
                                        ->label('Nama Pelapor')
                                        ->inlineLabel()
                                        ->visible(fn ($get) => $get('task')),
                                    DatePicker::make('date_in')
                                        ->label('Lapor')
                                        ->inlineLabel()
                                        ->visible(fn ($get) => $get('task'))
                                        ->default(Carbon::now())
                                        ->required()
                                        ->native(false),
                                    DatePicker::make('date_visit')
                                        ->label('Jadwal')
                                        ->inlineLabel()
                                        ->default(Carbon::now())
                                        ->required()
                                        ->native(false),
                                    Select::make('user_id')
                                        ->label('Support')
                                        ->inlineLabel()
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
                                ])->columnSpan(1),
                            Group::make()
                                ->schema([
                                    Toggle::make('task')
                                        ->label('Existing <-> New')
                                        ->live(),
                                    Fieldset::make('Produk')
                                        ->schema([
                                            Radio::make('product_id')
                                                ->label('Produk')
                                                ->columnSpanFull()
                                                ->disableLabel()
                                                ->visible(fn ($get) => $get('task'))
                                                ->options(fn (Get $get): Collection => Contract::query()
                                                    ->where('location_id', $get('location_id'))
                                                    ->join('products', 'products.id', '=', 'contracts.product_id')
                                                    ->pluck('products.name', 'products.id'))
                                                ->required(),
                                        ])
                                        ->visible(fn ($get) => $get('task'))
                                        ->reactive(),
                                    Fieldset::make('Status')
                                        ->schema([
                                            Checkbox::make('is_implement')
                                                ->label('Implementasi')
                                                ->visible(fn ($get) => $get('task'))
                                                ->inline(),
                                            Checkbox::make('is_oncall')
                                                ->label('OnCall')
                                                ->visible(fn ($get) => $get('task'))
                                                ->inline(),
                                        ])
                                        ->visible(fn ($get) => $get('task'))
                                        ->reactive(),
                                    CheckboxList::make('outstanding_id')
                                        ->label('Masalah')
                                        ->columnSpanFull()
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
                                ])->columnSpan(1),                            
                        ])->columns(2)
                ])
                ->modalHeading('Create Schedule')
                ->action(function (array $data, array $arguments): void {
                    if ($data['task']) {
                        // Create new outstanding  
                        $dataCreate =[
                            'number' => $data['number'],
                            'location_id' => $data['location_id'],
                            'product_id' => $data['product_id'],
                            'reporter' => $data['reporter'],
                            'reporter_name' => $data['reporter_name'],
                            'is_implement' => $data['is_implement'],
                            'is_oncall' => $data['is_oncall'],
                            'title' => $data['title'],
                            'date_in' => $data['date_in'],
                            'date_visit' => $data['date_visit'],
                        ];

                        if ($data['reporter'] === 'client') {
                            $dataCreate = array_merge($dataCreate, [
                                // 'reporter_name' => $data['reporter_name'],
                                'lpm' => $data['lpm'],
                            ]);
                        }

                        $outstanding = Outstanding::create($dataCreate);

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
                        // Attach multiple users to the reporting and send email
                        if (isset($data['user_id']) && is_array($data['user_id'])) {
                            $reporting->users()->attach($data['user_id']);

                            // Get necessary data for the email
                            $companyAlias = $location->company->alias ?? 'SAP';
                            $locationName = $location->name;
                            $title = $outstanding->title;
                            $dateVisit = $data['date_visit'];

                            // Collect all support emails
                            $supportEmails = User::whereIn('id', $data['user_id'])
                                ->pluck('email')
                                ->toArray();

                            // Dispatch a single email job with multiple recipients
                            if (!empty($supportEmails)) {
                                ScheduleMailJob::dispatch(
                                    $supportEmails,
                                    $dateVisit,
                                    $companyAlias,
                                    $locationName,
                                    $title
                                )->onQueue('scheduleEmails');
                            }
                        }

                        Notification::make()
                            ->title('Jadwal berhasil dibuat')
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
                            // Attach multiple users to the reporting and send email
                            if (isset($data['user_id']) && is_array($data['user_id'])) {
                                $reporting->users()->attach($data['user_id']);

                                // Get necessary data for the email
                                $companyAlias = $location->company->alias ?? 'SAP';
                                $locationName = $location->name;
                                $title = $outstanding->title;
                                $dateVisit = $data['date_visit'];

                                // Collect all support emails
                                $supportEmails = User::whereIn('id', $data['user_id'])
                                    ->pluck('email')
                                    ->toArray();

                                // Dispatch a single email job with multiple recipients
                                if (!empty($supportEmails)) {
                                    ScheduleMailJob::dispatch(
                                        $supportEmails,
                                        $dateVisit,
                                        $companyAlias,
                                        $locationName,
                                        $title
                                    )->onQueue('scheduleEmails');
                                }
                            }
                        }

                        Notification::make()
                            ->title('Jadwal berhasil dibuat')
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
