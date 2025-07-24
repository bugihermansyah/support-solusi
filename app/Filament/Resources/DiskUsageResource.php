<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiskUsageResource\Pages;
use App\Filament\Resources\DiskUsageResource\RelationManagers;
use App\Models\DiskUsage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class DiskUsageResource extends Resource
{
    protected static ?string $model = DiskUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
{
    // Ambil id terbaru (created_at paling akhir) untuk tiap location_id
    $latestIds = DB::table('disk_usages')
        ->select(DB::raw('MAX(id) as id'))
        ->groupBy('location_id');

    return parent::getEloquentQuery()
        ->whereIn('id', $latestIds)
        ->whereHas('location');
}

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
                TextColumn::make('location.name')->label('Location'),
                TextColumn::make('mount_point'),
                TextColumn::make('size'),
                TextColumn::make('used'),
                TextColumn::make('available'),
                TextColumn::make('usage_percent'),
                TextColumn::make('created_at')
                    ->label('Last Update')
                    ->dateTime(),
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
            'index' => Pages\ListDiskUsages::route('/'),
            // 'create' => Pages\CreateDiskUsage::route('/create'),
            // 'edit' => Pages\EditDiskUsage::route('/{record}/edit'),
        ];
    }
}
