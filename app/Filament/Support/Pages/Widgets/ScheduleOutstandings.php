<?php

namespace App\Filament\Support\Pages\Widgets;

use App\Enums\OutstandingTypeProblem;
use App\Filament\Resources\OutstandingResource;
use App\Filament\Support\Resources\StaffReportResource;
use App\Jobs\SupportMailJob;
use App\Models\Company;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Reporting;
use App\Models\StaffReport;
use App\Models\Unit;
use App\Models\User;
use App\Settings\MailSettings;
use Carbon\Carbon;
use Filament\Tables\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Wizard\Step;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater as ComponentsTableRepeater;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class ScheduleOutstandings extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getTableHeading(): string | Htmlable | null
    {
        return '';
    }

    public function getDisplayName(): string {
        return "Schedules";
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        return $table
            ->paginated(false)
            ->query(
                Reporting::whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->where('status', null)
            )
            ->columns([
                Split::make([
                    Tables\Columns\TextColumn::make('date_visit')
                        ->label('Jadwal')
                        ->icon('heroicon-m-calendar-days')
                        ->size(TextColumn\TextColumnSize::ExtraSmall)
                        ->weight(FontWeight::Bold)
                        ->grow(false)
                        ->date('d M Y'),
                        Split::make([
                    Tables\Columns\TextColumn::make('outstanding.location.name_alias')
                        ->label('Lokasi')
                        ->icon('heroicon-m-map-pin')
                        ->size(TextColumn\TextColumnSize::ExtraSmall)
                        ->weight(FontWeight::Bold)
                        ->grow(false),
                    Tables\Columns\TextColumn::make('outstanding.title')
                        ->label('Masalah')
                        ->icon('heroicon-m-briefcase')
                        ->size(TextColumn\TextColumnSize::ExtraSmall)
                        ])->from('md')
                ])
                ->from('md'),
                Panel::make([
                    Stack::make([
                        TextColumn::make('outstanding.reporter')
                            ->size(TextColumn\TextColumnSize::ExtraSmall)
                            ->icon('heroicon-m-phone'),
                        TextColumn::make('outstanding.reporter_name')
                            ->size(TextColumn\TextColumnSize::ExtraSmall)
                            ->grow(false),
                    ]),
                ])->collapsible(),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('start')
                    ->label('Start')
                    ->button()
                    ->action(function ($record) {
                        $record->update([
                            'start_work' => now(),
                        ]);
                    })
                    ->icon('heroicon-m-play-circle')
                    ->color('danger')
                    ->visible(fn(Model $record)=> !$record->start_work)
                    ->requiresConfirmation()
                    ->modalHeading('Start work')
                    ->modalDescription('Yakin anda akan memulai Outstanding ini?')
                    ->modalSubmitActionLabel('Yes, starting'),
                // Tables\Actions\Action::make('Open')
                //     ->label('Report v2')
                //     ->button()
                //     ->url(fn (Reporting $record): string => StaffReportResource::getUrl('edit', ['record' => $record])),
                EditAction::make('updateReport')
                    ->label('Report')
                    ->button()
                    ->visible(fn(Model $record)=> $record->start_work)
                    ->icon('heroicon-m-document-plus')
                    ->modalHeading('Reporting')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['end_work'] = Carbon::now();

                        return $data;
                    })
                    ->closeModalByClickingAway()
                    ->using(function (Model $record, array $data): Model {
                        $record->update($data);

                        return $record;
                    })
                    ->steps([
                        Step::make('Reporting')
                            ->schema([
                                Forms\Components\DatePicker::make('date_visit')
                                    ->label('Tanggal Aksi')
                                    ->hiddenLabel()
                                    ->default(Carbon::now())
                                    ->native(false)
                                    ->prefix('Action')
                                    ->columnSpanFull()
                                    ->required(),
                                Forms\Components\ToggleButtons::make('work')
                                    ->label('Jenis Aksi')
                                    ->inline()
                                    ->hiddenLabel()
                                    ->options([
                                        'visit' => 'Visit',
                                        'remote' => 'Remote'
                                    ])
                                    ->colors([
                                        'visit' => 'info',
                                        'remote' => 'warning',
                                    ])
                                    ->default('visit')
                                    ->grouped()
                                    ->required(),
                                Forms\Components\TextInput::make('cause')
                                    ->label('Sebab')
                                    ->hiddenLabel()
                                    ->placeholder('Sebab')
                                    ->required(),
                                Forms\Components\RichEditor::make('action')
                                    ->label('Aksi')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'bulletList',
                                        'italic',
                                        'orderedList',
                                    ])
                                    ->extraInputAttributes([
                                        'style' => 'min-height: 90px;',
                                    ]),
                                Forms\Components\RichEditor::make('note')
                                    ->label('Keterangan')
                                    ->toolbarButtons([
                                        'bold',
                                        'bulletList',
                                        'italic',
                                        'orderedList',
                                    ])
                                    ->extraInputAttributes([
                                        'style' => 'min-height: 50px;',
                                    ])
                                    ->columnSpanFull(),
                                    ]),
                        Step::make('Status')
                            ->schema([
                                Forms\Components\ToggleButtons::make('status')
                                    ->inline()
                                    ->live()
                                    ->hiddenLabel()
                                    ->options([
                                        '1' => 'Finish',
                                        '0' => 'Pending',
                                    ])
                                    ->icons([
                                        '1' => 'heroicon-o-check',
                                        '0' => 'heroicon-o-x-mark',
                                    ])
                                    ->colors([
                                        '1' => 'success',
                                        '0' => 'warning',
                                    ])
                                    ->default('1')
                                    ->helperText(new HtmlString('Jika <strong>Pending</strong> wajib isi Max Revisit'))
                                    ->grouped()
                                    ->required(),
                                Forms\Components\DatePicker::make('revisit')
                                    ->label('Revisit')
                                    ->hiddenLabel()
                                    ->placeholder('Revisit')
                                    ->required()
                                    ->hidden(fn ($get) => $get('status') !== '0')
                                    ->native(false),
                                Forms\Components\ToggleButtons::make('is_type_problem')
                                    ->label('Tipe Problem')
                                    ->hiddenLabel()
                                    ->helperText(new HtmlString('Setiap <strong>tipe problem</strong> wajib menyertakan kerusakan unit'))
                                    ->required()
                                    ->options(OutstandingTypeProblem::class)
                                    ->formatStateUsing(fn (Model $record) => $record->outstanding->is_type_problem ?? 'NON')
                                    ->inline(),
                                ComponentsTableRepeater::make('outstandingunits')
                                    ->label('Unit')
                                    ->hiddenLabel()
                                    ->relationship()
                                    ->addActionLabel('Tambah unit')
                                    ->reorderable(false)
                                    ->defaultItems(1)
                                    ->minItems(1)
                                    ->schema([
                                        Forms\Components\Select::make('unit_id')
                                            ->label('Unit')
                                            ->options(Unit::where('is_visible', 1)->pluck('name', 'id'))
                                            ->searchable()
                                            ->placeholder('Pilih unit')
                                            ->required()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                        Forms\Components\TextInput::make('qty')
                                            ->numeric()
                                            ->integer()
                                            ->default(1)
                                            ->required()
                                            ->maxValue(20)
                                            ->minValue(1),
                                    ])
                                    ->colStyles([
                                        'unit_id' => 'width: 1300px;',
                                        'qty' => 'width: 70px;',
                                    ])
                                    ->reorderable()
                                    ->collapsible()
                                    ->columnSpan('full'),
                                SpatieMediaLibraryFileUpload::make('attachments')
                                    ->image()
                                    ->multiple()
                                    ->resize(30)
                                    ->optimize('jpg')
                                    ->openable()
                                    ->maxSize(2048)
                                    ->maxFiles(10)
                                    ->preserveFilenames()
                                    ->columnSpanFull()
                                    ->previewable(false),
                                SignaturePad::make('signature')
                                    ->label(__('Sign'))
                                    ->dotSize(0.5)
                                    ->lineMinWidth(0.5)
                                    ->lineMaxWidth(1.0)
                                    ->throttle(16)
                                    ->minDistance(5)
                                    ->velocityFilterWeight(0.7)
                            ]),
                    ])
                    ->after(function (array $data, Model $record, array $arguments){

                        $report = Reporting::find($record->id);
                        $outstanding = Outstanding::find($report->outstanding_id);
                        $location = Location::find($outstanding->location_id);
                        $status = ($data['status'] == 1) ? 'Selesai' : 'Pending';

                        $user = auth()->user();

                        $userLocation = $outstanding->location?->user_id;

                        $sendUserHeadLocation = User::withRoleInSpecificLocation('Head', $location->id)->first();
                        $sendUserLocation = User::find($userLocation);

                        if ($outstanding) {
                            $outstanding->is_type_problem = $data['is_type_problem'];

                            $outstanding->save();
                        }

                        if ($data['status'] == 1) {
                            if ($outstanding) {
                                $outstanding->update([
                                    'date_finish' => $data['date_visit'],
                                    'status' => 1,
                                ]);
                            }
                        }

                        if($location->user_id !== null){
                            Notification::make()
                                ->title("{$user->firstname} {$user->lastname}")
                                ->icon('heroicon-o-document-plus')
                                ->body("membuat laporan <b>{$location->name} - {$outstanding->title}</b> status <b>{$status}</b>")
                                ->actions([
                                    ActionsAction::make('Lihat')
                                    ->url(OutstandingResource::getUrl('edit', ['record' => $outstanding], panel: 'support')),
                                ])
                                ->sendToDatabase($sendUserLocation);
                        }

                        if($sendUserHeadLocation){
                            Notification::make()
                                ->title("{$user->firstname} {$user->lastname}")
                                ->icon('heroicon-o-document-plus')
                                ->body("membuat laporan <b>{$location->name} - {$outstanding->title}</b> status <b>{$status}</b>")
                                ->actions([
                                    ActionsAction::make('Lihat')
                                    ->url(OutstandingResource::getUrl('edit', ['record' => $outstanding], panel: 'admin')),
                                ])
                                ->sendToDatabase($sendUserHeadLocation);
                        }

                            try {
                                $reporting = Reporting::find($record->id);
                                $mediaItems = $reporting->getMedia();

                                $settings = app(MailSettings::class);
                                $settings->loadMailSettingsToConfig($data);

                                $outstanding = Outstanding::find($record->outstanding_id);
                                $location = Location::find($outstanding->location_id);
                                $company = Company::find($location->company_id);
                                $dateLapor = Carbon::parse($outstanding->date_in)->format('d M Y');
                                $dateVisit = Carbon::parse($data['date_visit'])->format('d M Y');
                                $users = $reporting->users;
                                $supportNames = $users->pluck('firstname')->implode(', ');
                                $status = ($data['status'] == 1) ? 'Selesai' : 'Pending';

                                if ($status == 'Selesai') {
                                    $revisit = null;
                                }
                                if ($status == 'Pending') {
                                    $revisit = Carbon::parse($data['revisit'])->format('d M Y');
                                }

                                $user = auth()->user();

                                if ($user->team && $user->team->name === 'Barat') {
                                    $mailTo = $settings->to_barat;
                                    $mailCc = $settings->cc_barat;
                                } elseif ($user->team && $user->team->name === 'Timur') {
                                    $mailTo = $settings->to_timur;
                                    $mailCc = $settings->cc_timur;
                                } elseif ($user->team && $user->team->name === 'Pusat') {
                                    $mailTo = $settings->to_pusat;
                                    $mailCc = $settings->cc_pusat;
                                } elseif ($user->team && $user->team->name === 'CASS Barat') {
                                    $mailTo = $settings->to_cass_barat;
                                    $mailCc = $settings->cc_cass_barat;
                                } elseif ($user->team && $user->team->name === 'Luar Kota') {
                                    $mailTo = $settings->to_luar_kota;
                                    $mailCc = $settings->cc_luar_kota;
                                } else {
                                    $mailTo = $settings->to_address;
                                    $mailCc = [];
                                }

                                $mailData = [
                                    'number' => $outstanding->number,
                                    'company' => $company->alias,
                                    'location' => $location->name,
                                    'title' => $outstanding->title,
                                    'date_lapor' => $dateLapor,
                                    'date_visit' => $dateVisit,
                                    'work' => ucfirst($data['work']),
                                    'pelapor' => ucfirst($outstanding->reporter),
                                    'nama_pelapor' => $outstanding->number,
                                    'support' => $supportNames,
                                    'masalah' => $outstanding->title,
                                    'sebab' => $data['cause'],
                                    'aksi' => $data['action'],
                                    'status' => $status,
                                    'revisit' => $revisit,
                                    'note' => $data['note'],
                                    'attachments' => $mediaItems->map(function ($media) {
                                        return $media->getFullUrl();
                                    })->toArray(),
                                ];

                                SupportMailJob::dispatch($mailTo, $mailCc, $mailData)->onQueue('emails');

                                Notification::make()
                                    ->title('Email terkirim')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal mengirim email: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                    }),
            ]);
    }
}
