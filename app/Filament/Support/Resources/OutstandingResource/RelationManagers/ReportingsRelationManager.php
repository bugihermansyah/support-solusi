<?php

namespace App\Filament\Support\Resources\OutstandingResource\RelationManagers;

use App\Filament\Resources\OutstandingResource as ResourcesOutstandingResource;
use App\Filament\Support\Resources\OutstandingResource;
use App\Jobs\SupportMailJob;
use App\Models\Location;
use App\Models\Outstanding;
use App\Models\Reporting;
use App\Models\User;
use App\Settings\MailSettings;
use App\Tables\Columns\UserAvatarColumn;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ReportingsRelationManager extends RelationManager
{
    protected static string $relationship = 'reportings';

    protected static ?string $title = 'Laporan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\DatePicker::make('date_visit')
                                    ->label('Tanggal Aksi')
                                    ->default(Carbon::now())
                                    ->native(false)
                                    ->disabled()
                                    ->columnSpanFull()
                                    ->required(),
                                Forms\Components\ToggleButtons::make('work')
                                    ->label('Jenis Aksi')
                                    ->inline()
                                    ->disabled()
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
                                    ->disabled()
                                    ->options([
                                        '1' => 'Selesai',
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
                                    ->disabled()
                                    ->multiple()
                                    ->resize(30)
                                    ->optimize('webp')
                                    ->imagePreviewHeight('50')
                                    ->downloadable()
                                    ->openable()
                                    ->maxSize(7000)
                                    ->maxFiles(10)
                                    ->preserveFilenames()
                                    ->columnSpanFull()
                                    ->previewable()
                                    ->deletable(false),
                            ])
                            ->columns(2),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('cause')
                                    ->label('Sebab')
                                    ->disabled()
                                    ->required(),
                                Forms\Components\Textarea::make('action')
                                    ->label('Aksi')
                                    ->disabled()
                                    ->required()
                                    // ->toolbarButtons([
                                    //     'bold',
                                    //     'bulletList',
                                    //     'italic',
                                    //     'orderedList',
                                    // ])
                                    ->extraInputAttributes([
                                        'style' => 'min-height: 90px;',
                                    ]),
                                Forms\Components\Textarea::make('solution')
                                    ->label('Solusi')
                                    ->disabled()
                                    // ->toolbarButtons([
                                    //     'bold',
                                    //     'bulletList',
                                    //     'italic',
                                    //     'orderedList',
                                    // ])
                                    ->extraInputAttributes([
                                        'style' => 'min-height: 70px;',
                                    ]),

                                Forms\Components\Textarea::make('note')
                                    ->label('Keterangan')
                                    ->disabled()
                                    // ->toolbarButtons([
                                    //     'bold',
                                    //     'bulletList',
                                    //     'italic',
                                    //     'orderedList',
                                    // ])
                                    ->extraInputAttributes([
                                        'style' => 'min-height: 50px;',
                                    ])
                                    ->columnSpanFull(),
                            ]),
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
                    ->searchable()
                    ->sortable()
                    ->date(),
                UserAvatarColumn::make('user')
                    ->label('Support')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gap')
                    ->label('')
                    ->visible('sm'),
                Tables\Columns\TextColumn::make('work')
                    ->formatStateUsing(fn (string $state): string => ucwords($state))
                    ->label('Tipe Aksi'),
                Tables\Columns\TextColumn::make('cause')
                    ->label('Sebab')
                    ->wrap()
                    ->html(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->wrap()
                    ->html(),
                Tables\Columns\TextColumn::make('solution')
                    ->label('Solusi')
                    ->wrap()
                    ->html(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Keterangan')
                    ->wrap()
                    ->html(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()
                //     ->mutateFormDataUsing(function (array $data): array {
                //         $data['user_id'] = auth()->id();
                //         $data['outstanding_id'] = $this->ownerRecord->id;

                //         return $data;
                //     })
                //     ->using(function (array $data, string $model): Model {
                //         return $model::create($data);
                //     })
                //     ->extraModalFooterActions(fn (Action $action): array => [
                //         // $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                //         //     ->label(__('filament-actions::create.single.modal.actions.create_another.label')),
                //         $action->makeModalSubmitAction('sendEmailAction', ['sendEmailArgument' => true])
                //             ->label('Buat & Kirim email')
                //     ])
                //     ->after(function (array $data, Model $record, array $arguments){

                //         $report = Reporting::find($record->id);
                //         $outstanding = Outstanding::find($report->outstanding_id);
                //         $location = Location::find($outstanding->location_id);
                //         $status = ($data['status'] == 1) ? 'Selesai' : 'Pending';

                //         $user = auth()->user();

                //         $userLocation = $outstanding->location?->user_id;

                //         $sendUserHeadLocation = User::withRoleInSpecificLocation('Head', $location->id)->first();
                //         $sendUserLocation = User::find($userLocation);

                //         if($location->user_id !== null) {
                //             Notification::make()
                //                 ->title("{$user->firstname} {$user->lastname}")
                //                 ->icon('heroicon-o-document-plus')
                //                 ->body("membuat laporan <b>{$location->name} - {$outstanding->title}</b> status <b>{$status}</b>")
                //                 ->actions([
                //                     ActionsAction::make('Lihat')
                //                     ->url(OutstandingResource::getUrl('edit', ['record' => $outstanding], panel: 'support')),
                //                 ])
                //                 ->sendToDatabase($sendUserLocation);
                //         }

                //         Notification::make()
                //             ->title("{$user->firstname} {$user->lastname}")
                //             ->icon('heroicon-o-document-plus')
                //             ->body("membuat laporan <b>{$location->name} - {$outstanding->title}</b> status <b>{$status}</b>")
                //             ->actions([
                //                 ActionsAction::make('Lihat')
                //                 ->url(ResourcesOutstandingResource::getUrl('edit', ['record' => $outstanding], panel: 'admin')),
                //             ])
                //             ->sendToDatabase($sendUserHeadLocation);

                //         if($arguments['sendEmailArgument'] ?? false){

                //             try {
                //                 $reporting = Reporting::find($record->id);
                //                 $mediaItems = $reporting->getMedia();

                //                 // dd($record->id, $reporting, $mediaItems);
                //                 $settings = app(MailSettings::class);
                //                 $settings->loadMailSettingsToConfig($data);

                //                 $outstanding = Outstanding::find($record->outstanding_id);
                //                 $location = Location::find($outstanding->location_id);
                //                 $dateLapor = Carbon::parse($outstanding->date_in)->format('d M Y');
                //                 $dateVisit = Carbon::parse($data['date_visit'])->format('d M Y');
                //                 $user = User::find($data['user_id']);
                //                 $status = ($data['status'] == 1) ? 'Selesai' : 'Pending';

                //                 $user = auth()->user();

                //                 if ($user->team && $user->team->name === 'Barat') {
                //                     $mailTo = $settings->to_barat;
                //                     $mailCc = $settings->cc_barat;
                //                 } elseif ($user->team && $user->team->name === 'Timur') {
                //                     $mailTo = $settings->to_timur;
                //                     $mailCc = $settings->cc_timur;
                //                 } elseif ($user->team && $user->team->name === 'Pusat') {
                //                     $mailTo = $settings->to_pusat;
                //                     $mailCc = $settings->cc_pusat;
                //                 } elseif ($user->team && $user->team->name === 'CASS Barat') {
                //                     $mailTo = $settings->to_cass_barat;
                //                     $mailCc = $settings->cc_cass_barat;
                //                 } else {
                //                     $mailTo = null;
                //                     $mailCc = [];
                //                 }

                //                 $mailData = [
                //                     'location' => $location->name,
                //                     'title' => $outstanding->title,
                //                     'date_lapor' => $dateLapor,
                //                     'date_visit' => $dateVisit,
                //                     'work' => $data['work'],
                //                     'pelapor' => $outstanding->reporter,
                //                     'support' => $user->firstname,
                //                     'masalah' => $outstanding->title,
                //                     'sebab' => $data['cause'],
                //                     'aksi' => $data['action'],
                //                     'solusi' => $data['solution'],
                //                     'status' => $status,
                //                     'note' => $data['note'],
                //                     'attachments' => $mediaItems->map(function ($media) {
                //                         return $media->getFullUrl();
                //                     })->toArray(),
                //                 ];

                //                 SupportMailJob::dispatch($mailTo, $mailCc, $mailData)->onQueue('emails');

                //                 Notification::make()
                //                     ->title('Email terkirim')
                //                     ->success()
                //                     ->send();
                //             } catch (\Exception $e) {
                //                 Notification::make()
                //                     ->title('Gagal mengirim email: ' . $e->getMessage())
                //                     ->danger()
                //                     ->send();
                //             }
                //         }
                //     }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Lihat'),
                    // ->visible(function ($record) {
                    //     return $record->user_id === Auth::id();
                    // }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
