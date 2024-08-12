<?php

namespace App\Filament\Resources\Warehouse;

use App\Filament\Resources\Warehouse\StockResource\Pages;
use App\Filament\Resources\Warehouse\StockResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $modelLabel = 'Stock';

    // protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Gudang';

    protected static ?int $navigationSort = 4;

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
            ->query(
                Unit::where('is_warehouse', 1)
            )
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->square(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('stock')
                    ->label('Stock'),
                TextColumn::make('pinjam')
                    ->label('Keluar')
                    ->getStateUsing(function ($record) {
                        return \App\Models\LoanUnit::where('warehouse_loan_units.unit_id', $record->id)
                            ->leftJoin('warehouse_return_units', function ($join) {
                                $join->on('warehouse_loan_units.loan_id', '=', 'warehouse_return_units.loan_id')
                                    ->on('warehouse_loan_units.unit_id', '=', 'warehouse_return_units.unit_id')
                                    ->whereNotNull('warehouse_return_units.accepted_at');
                            })
                            ->selectRaw('SUM(warehouse_loan_units.qty) - IFNULL(SUM(warehouse_return_units.qty), 0) as total_pinjam')
                            ->value('total_pinjam') ?? 0;
                    }),
                TextColumn::make('sisa')
                    ->label('Sisa')
                    ->getStateUsing(function ($record) {
                        $totalPinjam = \App\Models\LoanUnit::where('warehouse_loan_units.unit_id', $record->id)
                            ->leftJoin('warehouse_return_units', function ($join) {
                                $join->on('warehouse_loan_units.loan_id', '=', 'warehouse_return_units.loan_id')
                                    ->on('warehouse_loan_units.unit_id', '=', 'warehouse_return_units.unit_id')
                                    ->whereNotNull('warehouse_return_units.accepted_at');
                            })
                            ->selectRaw('SUM(warehouse_loan_units.qty) - IFNULL(SUM(warehouse_return_units.qty), 0) as total_pinjam')
                            ->value('total_pinjam') ?? 0;

                        return $record->stock - $totalPinjam;
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStocks::route('/'),
        ];
    }
}
