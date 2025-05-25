<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportDailyResource\Pages;
use App\Models\Customer;
use App\Models\Reporting;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ReportDailyResource extends Resource
{
    protected static ?string $model = Reporting::class;

    protected static ?string $modelLabel = 'Reporting Daily';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Daily';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Info')
                            ->schema([
                                Placeholder::make('number')
                                    ->label('No. Tiket')
                                    ->content(fn (Reporting $record): string => $record->outstanding->number),
                                Placeholder::make('location')
                                    ->label('Lokasi')
                                    ->content(fn (Reporting $record): string => $record->outstanding->location->name),
                                Placeholder::make('title')
                                    ->label('Masalah')
                                    ->content(fn (Reporting $record): string => $record->outstanding->title),
                                Placeholder::make('date_visit')
                                    ->label('Tanggal Aksi')
                                    ->content(fn (Reporting $record): string => $record->date_visit),
                                Placeholder::make('user_id')
                                    ->label('Support')
                                    ->content(fn (Reporting $record): string => $record->users()->pluck('firstname')->join(', ')),
                                Placeholder::make('status')
                                    ->label('Status')
                                    ->content(fn (Reporting $record): ?string => $record->status->name),
                                Placeholder::make('mail')
                                    ->label('Mail')
                                    ->content(fn (Reporting $record): ?string => $record->send_mail_at),
                                Placeholder::make('signature')
                                    ->content(function (Reporting $record): HtmlString {
                                        // Mengambil data base64 tanda tangan dari database
                                        $signatureBase64 = $record->signature;

                                        // Pastikan ada data base64 untuk tanda tangan
                                        if ($signatureBase64) {
                                            return new HtmlString('<img src="' . $signatureBase64 . '" alt="Tanda Tangan" style="max-width: 100%; height: auto;"/>');
                                        }

                                        // Jika tidak ada tanda tangan, tampilkan pesan alternatif
                                        return new HtmlString('<p>No signature available</p>');
                                    }),
                            ])
                            ->columnSpan(['lg' => 1])
                            ->hidden(fn (?Reporting $record) => $record === null),
                    ])
                    ->columnSpan(['lg' => 1]),
                Group::make()
                    ->schema([
                        Select::make('email_to')
                            ->label('Email To')
                            ->multiple()
                            ->required()
                            ->options(Customer::all()->pluck('name_email', 'email')),
                        Select::make('email_cc')
                            ->label('Email CC')
                            ->multiple()
                            ->required()
                            ->options(Customer::all()->pluck('name_email', 'email')),
                        TextInput::make('cause')
                            ->required()
                            ->label('Sebab'),
                        RichEditor::make('action')
                            ->label('Aksi')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'orderedList',
                                'strike',
                                'underline',
                            ]),
                        RichEditor::make('note')
                            ->label('Keterangan')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'orderedList',
                                'strike',
                                'underline',
                            ])
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('attachments')
                            ->imageEditor()
                            ->image()
                            ->imagePreviewHeight('200')
                            ->multiple()
                            ->optimize('jpg')
                            ->maxSize(2048)
                            ->maxFiles(10)
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames()
                            ->previewable(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date_visit', 'desc')
            ->columns([
                TextColumn::make('outstanding.location.name')
                    ->label('Lokasi')
                    ->limit(15)
                    ->searchable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('outstanding.reporter')
                    ->label('Pelapor')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucwords($state)),
                TextColumn::make('users.firstname')
                    ->label('Support')
                    ->searchable()
                    ->listWithLineBreaks(),
                TextColumn::make('date_visit')
                    ->label('Tgl Aksi')
                    ->date(),
                TextColumn::make('outstanding.title')
                    ->label('Masalah')
                    ->limit(15)
                    ->searchable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('cause')
                    ->label('Sebab')
                    ->limit(15)
                    ->searchable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->html(),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->lineClamp(4)
                    ->searchable()
                    ->words(10)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return strip_tags($state);
                    })
                    ->html(),
                TextColumn::make('status'),
                IconColumn::make('send_mail_at')
                    ->label('Mail')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(function ($record) {
                        return !is_null($record->send_mail_at);
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->hidden(fn(Reporting $record) => !$record->status)
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportDailies::route('/'),
            'create' => Pages\CreateReportDaily::route('/create'),
            'edit' => Pages\EditReportDaily::route('/{record}/edit'),
        ];
    }
}
