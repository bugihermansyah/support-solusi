<?php

namespace App\Filament\Support\Resources\OutstandingResource\Pages;

use App\Filament\Support\Resources\OutstandingResource;
use App\Models\Reporting;
use App\Models\Team;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageOutstandingReport extends ManageRelatedRecords
{
    protected static string $resource = OutstandingResource::class;

    protected static string $relationship = 'reportings';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    public function getTitle(): string | Htmlable
    {
        $recordTitle = $this->getRecordTitle();

        $recordTitle = $recordTitle instanceof Htmlable ? $recordTitle->toHtml() : $recordTitle;

        return "Masalah: {$recordTitle}";
    }

    public function getBreadcrumb(): string
    {
        return 'Reportings';
    }

    public static function getNavigationLabel(): string
    {
        return 'Manage Reportings';
    }

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
                            ->options(function () {
                                $teams = Team::with('users')->get();
                                $options = [];
                                foreach ($teams as $team) {
                                    $teamUsers = $team->users->pluck('name', 'id')->toArray();
                                    $options[$team->name] = $teamUsers;
                                }
                                return $options;
                            })
                            ->searchable()
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
                            // ->disabled()
                            ->multiple()
                            ->resize(30)
                            ->optimize('jpg')
                            ->imagePreviewHeight('50')
                            ->downloadable()
                            ->openable()
                            ->maxSize(2048)
                            ->maxFiles(10)
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->previewable(),
                            // ->deletable(false),
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
                        // SpatieMediaLibraryFileUpload::make('attachments')
                        //     ->multiple()
                        //     ->preserveFilenames()
                        //     ->previewable(false),
                    ]),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                SpatieMediaLibraryImageEntry::make('attachments')
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
                Tables\Columns\TextColumn::make('users.firstname')
                    ->label('Support')
                    ->listWithLineBreaks()
                    ->limitList(1),
                    // ->expandableLimitedList(),
                Tables\Columns\TextColumn::make('work')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => ucwords($state)),
                Tables\Columns\TextColumn::make('cause')
                    ->label('Sebab')
                    ->html(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->html(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('revisit')
                    ->label('Revisit')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('attachments')
                    ->getStateUsing(function (Reporting $record){
                        return $record->getFirstMedia() ? true : false;
                    })
                    ->label('Berkas')
                    ->boolean(),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hiddenLabel()->icon('heroicon-m-folder'),
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Ubah'),
                Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('Hapus'),

            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                //     Tables\Actions\ForceDeleteBulkAction::make(),
                //     Tables\Actions\RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
