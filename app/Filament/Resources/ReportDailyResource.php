<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportDailyResource\Pages;
use App\Filament\Resources\ReportDailyResource\RelationManagers;
use App\Models\Reporting;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportDailyResource extends Resource
{
    protected static ?string $model = Reporting::class;

    protected static ?string $modelLabel = 'Reporting Daily';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Info')
                            ->schema([
                                Placeholder::make('date_visit')
                                    ->label('Tanggal Aksi')
                                    ->content(fn (Reporting $record): string => $record->date_visit),
                                Placeholder::make('user_id')
                                    ->label('Support')
                                    ->content(fn (Reporting $record): string => $record->users()->pluck('firstname')->join(', ')),
                                Placeholder::make('status')
                                    ->label('Status')
                                    ->content(fn (Reporting $record): ?string => $record->status->name),
                            ])
                            ->columnSpan(['lg' => 1])
                            ->hidden(fn (?Reporting $record) => $record === null),
                    ])
                    ->columnSpan(['lg' => 1]),
                Group::make()
                    ->schema([
                        TextInput::make('cause'),
                        RichEditor::make('action')
                            ->label('Aksi'),
                        RichEditor::make('note')
                            ->label('Keterangan')
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('attachments')
                            ->imagePreviewHeight('200')
                            ->downloadable()
                            ->openable()
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
                    ->label('Lokasi'),
                TextColumn::make('users.firstname')
                    ->label('Support'),
                TextColumn::make('date_visit')
                    ->label('Tgl Aksi')
                    ->date(),
                TextColumn::make('outstanding.title')
                    ->label('Masalah'),
                TextColumn::make('cause')
                    ->label('Sebab')
                    ->html(),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->html(),
                TextColumn::make('status'),
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
