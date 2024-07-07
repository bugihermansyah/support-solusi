<?php

namespace App\Filament\Support\Widgets;

use App\Filament\Support\Resources\OutstandingResource;
use App\Jobs\SupportMailJob;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Reporting;
use App\Models\Team;
use App\Models\User;
use App\Settings\MailSettings;
use Carbon\Carbon;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OpenOutstandings extends BaseWidget
{
    public function table(Table $table): Table
    {
        $user = Auth::user();
        return $table
            ->query(
                Outstanding::query()
                ->whereHas('location', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('status', 0)

            )
            ->columns([
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Masalah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_in')
                    ->label('Lapor')
                    ->formatStateUsing(function ($state) {
                        $createdDate = Carbon::parse($state);
                        $daysDifference = $createdDate->subDays(1)->longAbsoluteDiffForHumans();
                        return $daysDifference;
                    }),
                Tables\Columns\TextColumn::make('reportings_count')
                    ->label('Aksi')
                    ->prefix('x')
                    ->sortable()
                    ->counts('reportings'),
                ])
            ->actions([
                Action::make('createReport')
                    ->label('Report')
                    ->modalHeading('Buat Laporan')
                    ->form([
                        Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\DatePicker::make('date_visit')
                                        ->label('Tanggal Aksi')
                                        ->default(Carbon::now())
                                        ->native(false)
                                        ->required(),
                                    Forms\Components\Select::make('user_id')
                                        ->label('Support')
                                        ->options(User::all()->pluck('firstname', 'id'))
                                        ->default(Auth::user()->id)
                                        ->disabled()
                                        ->dehydrated()
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
                                        ->maxSize(2048)
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
                                        ->toolbarButtons([
                                            'bold',
                                            'bulletList',
                                            'italic',
                                            'orderedList',
                                        ])
                                        ->extraInputAttributes([
                                            'style' => 'min-height: 100px;',
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
                                            'style' => 'min-height: 100px;',
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
                                            'style' => 'min-height: 100px;',
                                        ])
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    ])
                    ->action(function (array $data, Outstanding $record) {
                        Reporting::create([
                            'outstanding_id' => $record->id,
                            'cause' => $data['cause'],
                            'action' => $data['action'],
                            'solution' => $data['solution'],
                            'work' => $data['work'],
                            'date_visit' => $data['date_visit'],
                            'user_id' => $data['user_id'],
                            'status' => $data['status'],
                            'note' => $data['note']
                        ]);

                        Notification::make()
                            ->title('Laporan berhasil dibuat')
                            ->success()
                            ->send();
                    })
                    ->extraModalFooterActions(fn (Action $action): array => [
                        $action->makeModalSubmitAction('sendEmailAction', ['sendEmailArgument' => true])
                            ->label('Buat & Kirim email')
                    ])
                    ->after(function (array $data, Model $record, array $arguments){
                        if($arguments['sendEmailArgument'] ?? false){

                            try {
                                $reporting = Reporting::find($record->id);
                                $mediaItems = $reporting->getMedia();

                                $settings = app(MailSettings::class);
                                $settings->loadMailSettingsToConfig($data);

                                $outstanding = Outstanding::find($record->outstanding_id);
                                $location = Location::find($outstanding->location_id);
                                $dateLapor = Carbon::parse($outstanding->date_in)->format('d M Y');
                                $dateVisit = Carbon::parse($data['date_visit'])->format('d M Y');
                                $user = User::find($data['user_id']);
                                $status = ($data['status'] == 1) ? 'Selesai' : 'Pending';

                                $user = auth()->user();
                                // Tentukan nilai $mailTo dan $mailCC berdasarkan tim pengguna
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
                                } else {
                                    // Default atau tangani jika pengguna tidak termasuk dalam tim yang diharapkan
                                    $mailTo = null;
                                    $mailCc = [];
                                }

                                $mailData = [
                                    'location' => $location->name,
                                    'title' => $outstanding->title,
                                    'date_lapor' => $dateLapor,
                                    'date_visit' => $dateVisit,
                                    'work' => $data['work'],
                                    'pelapor' => $outstanding->reporter,
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
                                // Mail::to($mailTo)->cc($mailCc)->send(new SupportMail($mailData));

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
