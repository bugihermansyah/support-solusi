<?php

namespace App\Filament\Support\Resources\OutstandingResource\RelationManagers;

use App\Jobs\SupportMailJob;
use App\Mail\SupportMail;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Reporting;
use App\Models\Team;
use App\Models\User;
use App\Settings\MailSettings;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ReportingsRelationManager extends RelationManager
{
    protected static string $relationship = 'reportings';

    protected static ?string $title = 'Laporan';

    public function form(Form $form): Form
    {
        return $form
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
                            ->disabled(),
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
                    ])
                    ->columns(),

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
                        SpatieMediaLibraryFileUpload::make('attachments')
                            ->multiple()
                            ->preserveFilenames()
                            ->previewable(false),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('cause')
            ->columns([
                Tables\Columns\TextColumn::make('date_visit')
                    ->label('Tanggal')
                    ->date(),
                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Support'),
                Tables\Columns\TextColumn::make('work')
                    ->label('Tipe Aksi'),
                Tables\Columns\TextColumn::make('cause')
                    ->label('Sebab')
                    ->html(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->html(),
                Tables\Columns\TextColumn::make('solution')
                    ->label('Solusi')
                    ->html(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['outstanding_id'] = $this->ownerRecord->id;

                        return $data;
                    })
                    ->using(function (array $data, string $model): Model {
                        return $model::create($data);
                    })
                    ->extraModalFooterActions(fn (Action $action): array => [
                        // $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                        //     ->label(__('filament-actions::create.single.modal.actions.create_another.label')),
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
                                } elseif ($user->team && $user->team->name === 'Pusat') {
                                    $mailTo = $settings->to_timur;
                                    $mailCc = $settings->cc_timur;
                                } elseif ($user->team && $user->team->name === 'Timur') {
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
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Ubah'),
                Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
