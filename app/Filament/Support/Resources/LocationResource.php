<?php

namespace App\Filament\Support\Resources;

use App\Filament\Support\Resources\LocationResource\Pages;
use App\Filament\Support\Resources\LocationResource\RelationManagers;
use App\Filament\Support\Resources\LocationResource\Widgets\LocationsOverview;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    // protected static ?string $pluralLabel = 'Lokasi';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.alias')
                    ->label('Group')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Lokasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contracts.product.name')
                    ->label('Produk')
                    ->searchable()
                    ->listWithLineBreaks()
                    ->badge(),
                Tables\Columns\TextColumn::make('contracts.bap')
                    ->label('BAP')
                    ->searchable()
                    ->date()
                    ->listWithLineBreaks()
                    ->badge(),
                Tables\Columns\TextColumn::make('contracts.type_contract')
                    ->label('Kontrak')
                    ->searchable()
                    ->listWithLineBreaks()
                    ->badge(),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Area')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bd.firstname')
                    ->label('BD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Support')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type_contract')
                    ->label('Kontrak'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),
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
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            LocationsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            // 'create' => Pages\CreateLocation::route('/create'),
            // 'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
