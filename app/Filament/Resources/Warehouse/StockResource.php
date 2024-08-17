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

    protected static ?string $navigationGroup = 'Warehouse';

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
                        return \App\Models\LoanUnit::where('unit_id', $record->id)
                            ->whereHas('loan', function ($query) {
                                $query->whereNotNull('processed_at')
                                      ->whereNull('rejected_at')
                                      ->whereNull('completed_at');
                            })
                            ->selectRaw('SUM(qty) - IFNULL(SUM(return_qty), 0) as total_pinjam')
                            ->value('total_pinjam') ?? 0;
                    }),
                TextColumn::make('sisa')
                    ->label('Sisa')
                    ->getStateUsing(function ($record) {
                        // Menghitung total pinjaman bersih (qty - return_qty)
                        $totalPinjaman = \App\Models\LoanUnit::where('unit_id', $record->id)
                            ->whereHas('loan', function ($query) {
                                $query->whereNotNull('processed_at')  // Hanya Loans yang diproses
                                    ->whereNull('rejected_at')      // Tidak termasuk Loans yang ditolak
                                    ->whereNull('completed_at');    // Tidak termasuk Loans yang sudah selesai
                            })
                            ->selectRaw('SUM(qty - return_qty) as total_pinjam')
                            ->value('total_pinjam') ?? 0;

                        // Menghitung sisa unit dari stock yang ada di tabel units
                        $sisaUnit = $record->stock - $totalPinjaman;

                        return $sisaUnit;
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
