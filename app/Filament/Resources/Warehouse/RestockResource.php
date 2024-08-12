<?php

namespace App\Filament\Resources\Warehouse;

use App\Filament\Resources\Warehouse\RestockResource\Pages;
use App\Filament\Resources\Warehouse\RestockResource\RelationManagers;
use App\Models\Restock;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RestockResource extends Resource
{
    protected static ?string $model = Restock::class;

    protected static ?string $modelLabel = 'Restock';

    // protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Gudang';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('unit_id')
                    ->label('Unit')
                    ->options(Unit::where('is_warehouse', 1)->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('qty')
                    ->label('Qty')
                    ->numeric(),
                Textarea::make('note')
                    ->label('Ket.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit.name')
                    ->label('Unit'),
                TextColumn::make('qty')
                    ->label('Qty'),
                TextColumn::make('user.name')
                    ->label('User'),
                TextColumn::make('note')
                    ->label('Ket.'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRestocks::route('/'),
        ];
    }
}
