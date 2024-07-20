<?php

namespace App\Filament\Support\Widgets;

use App\Filament\Resources\OutstandingResource;
use App\Jobs\SupportMailJob;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\OutstandingUnit;
use App\Models\Reporting;
use App\Models\Unit;
use App\Models\User;
use App\Settings\MailSettings;
use Carbon\Carbon;
use Filament\Tables\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ScheduleOutstandings extends BaseWidget
{
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        $user = Auth::user();
        return $table
            ->defaultPaginationPageOption(5)
            ->query(
                Reporting::query()
                    ->where('user_id', $user->id)
                    ->where('status', null)
            )
            ->columns([
                Split::make([
                    Tables\Columns\TextColumn::make('date_visit')
                        ->label('Jadwal')
                        ->icon('heroicon-m-briefcase')
                        ->weight(FontWeight::Bold)
                        ->grow(false)
                        ->date(),
                        Split::make([
                    Tables\Columns\TextColumn::make('outstanding.location.name')
                        ->label('Lokasi')
                        ->icon('heroicon-m-map-pin')
                        ->weight(FontWeight::Bold)
                        ->grow(false)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('outstanding.title')
                        ->label('Masalah')
                        ->icon('heroicon-m-inbox-arrow-down')
                        ->searchable(),
                        ])->from('md')
                ])
                ->from('md'),
            ])
            ->actions([
                EditAction::make('updateReport')
                    ->label('Report')
                    ->icon('heroicon-m-document-plus')
                    ->modalHeading('Buat Laporan')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        $record->update($data);

                        return $record;
                    })
                    ->form([
                        Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\DatePicker::make('date_visit')
                                        ->label('Tanggal Aksi')
                                        ->default(Carbon::now())
                                        ->native(false)
                                        ->columnSpanFull()
                                        ->required(),
                                    Forms\Components\ToggleButtons::make('work')
                                        ->label('Jenis Aksi')
                                        ->inline()
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
                                    Forms\Components\ToggleButtons::make('status')
                                        ->inline()
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
                                        ->grouped()
                                        ->required(),
                                    SpatieMediaLibraryFileUpload::make('attachments')
                                        ->image()
                                        ->multiple()
                                        ->resize(30)
                                        ->optimize('jpg')
                                        ->openable()
                                        ->maxSize(2500)
                                        ->maxFiles(10)
                                        ->preserveFilenames()
                                        ->columnSpanFull()
                                        ->previewable(false),
                                ])
                                ->columns(2),

                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\TextInput::make('cause')
                                        ->label('Sebab')
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
                                    Forms\Components\RichEditor::make('solution')
                                        ->label('Solusi')
                                        ->toolbarButtons([
                                            'bold',
                                            'bulletList',
                                            'italic',
                                            'orderedList',
                                        ])
                                        ->extraInputAttributes([
                                            'style' => 'min-height: 70px;',
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
                        ]),
                    ])
                    ->extraModalFooterActions(fn (Action $action): array => [
                        $action->makeModalSubmitAction('sendEmailAction', ['sendEmailArgument' => true])
                            ->label('Simpan & Kirim email')
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

                        Notification::make()
                            ->title("{$user->firstname} {$user->lastname}")
                            ->icon('heroicon-o-document-plus')
                            ->body("membuat laporan <b>{$location->name} - {$outstanding->title}</b> status <b>{$status}</b>")
                            ->actions([
                                ActionsAction::make('Lihat')
                                ->url(OutstandingResource::getUrl('edit', ['record' => $outstanding], panel: 'admin')),
                            ])
                            ->sendToDatabase($sendUserHeadLocation);

                        if($arguments['sendEmailArgument'] ?? false){

                            try {
                                $reporting = Reporting::find($record->id);
                                $mediaItems = $reporting->getMedia();

                                // dd($record->id, $reporting, $mediaItems);
                                $settings = app(MailSettings::class);
                                $settings->loadMailSettingsToConfig($data);

                                $outstanding = Outstanding::find($record->outstanding_id);
                                $location = Location::find($outstanding->location_id);
                                $dateLapor = Carbon::parse($outstanding->date_in)->format('d M Y');
                                $dateVisit = Carbon::parse($data['date_visit'])->format('d M Y');
                                $user = User::find($data['user_id']);
                                $status = ($data['status'] == 1) ? 'Selesai' : 'Pending';

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
                                    'location' => $location->name,
                                    'title' => $outstanding->title,
                                    'date_lapor' => $dateLapor,
                                    'date_visit' => $dateVisit,
                                    'work' => ucfirst($data['work']),
                                    'pelapor' => ucfirst($outstanding->reporter),
                                    'support' => $user->firstname,
                                    'masalah' => $outstanding->title,
                                    'sebab' => $data['cause'],
                                    'aksi' => $data['action'],
                                    'solusi' => $data['solution'],
                                    'status' => $status,
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
                        }
                    }),
            ]);
    }
}
