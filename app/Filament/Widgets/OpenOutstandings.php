<?php

namespace App\Filament\Widgets;

use App\Models\Outstanding;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OpenOutstandings extends BaseWidget
{
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(
                Outstanding::query()
            )
            ->columns([
                TextColumn::make('location.name')
                    ->label('Lokasi'),
                TextColumn::make('title')
                    ->label('Masalah'),
            ]);
    }
}
