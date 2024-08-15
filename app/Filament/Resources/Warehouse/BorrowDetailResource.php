<?php

namespace App\Filament\Resources\Warehouse;

use App\Filament\Resources\Warehouse\BorrowDetailResource\Pages;
use App\Filament\Resources\Warehouse\BorrowDetailResource\RelationManagers;
use App\Models\LoanUnit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BorrowDetailResource extends Resource
{
    protected static ?string $model = LoanUnit::class;

    protected static ?string $modelLabel = 'Peminjaman';

    // protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Gudang';

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
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->searchable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('loan.number')
                    ->label('No. Request')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loan.user.name')
                    ->label('Peminjam')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Nama Unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->searchable(),
                Tables\Columns\TextColumn::make('return_qty')
                    ->label('Return')
                    ->numeric()
                    ->searchable(),
                // Tables\Columns\TextColumn::make('status')
                //     ->label('Status')
                //     ->badge()
                //     ->getStateUsing(function ($record) {
                //         if ($record->rejected_at) {
                //             return 'Ditolak';
                //         }

                //         if ($record->accepted_at) {
                //             return 'Diterima';
                //         }

                //         return 'Pending';
                //     })
                //     ->icons([
                //         'heroicon-o-x-circle' => fn ($state): bool => $state === 'Ditolak',
                //         'heroicon-o-check-circle' => fn ($state): bool => $state === 'Diterima',
                //     ])
                //     ->colors([
                //         'danger' => 'Ditolak',
                //         'success' => 'Diterima',
                //     ]),
                // Tables\Columns\TextColumn::make('comment')
                //     ->label('Catatan'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBorrowDetails::route('/'),
            // 'create' => Pages\CreateBorrowDetail::route('/create'),
            // 'edit' => Pages\EditBorrowDetail::route('/{record}/edit'),
        ];
    }
}
