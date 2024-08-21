<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailAdressResource\Pages;
use App\Filament\Resources\EmailAdressResource\RelationManagers\LocationsRelationManager;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmailAdressResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $pluralLabel = 'Alamat Email';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Main';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tlp')
                    ->label('Phone')
                    ->tel()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(100),
                Forms\Components\Textarea::make('description')
                    ->label('Keterangan'),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tlp')
                    ->label('Phone')
                    ->numeric()
                    ->copyable()
                    ->copyMessage('Phone Number copied')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email address copied')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('locations_count')
                    ->label('Lokasi')
                    ->counts('locations'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Ubah'),
                Tables\Actions\DeleteAction::make()->hiddenLabel()->tooltip('Hapus'),
                Tables\Actions\ForceDeleteAction::make()->hiddenLabel()->tooltip('Hapus selamanya'),
                Tables\Actions\RestoreAction::make()->hiddenLabel()->tooltip('Kembalikan data'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LocationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailAdresses::route('/'),
            'create' => Pages\CreateEmailAdress::route('/create'),
            'edit' => Pages\EditEmailAdress::route('/{record}/edit'),
        ];
    }
}
