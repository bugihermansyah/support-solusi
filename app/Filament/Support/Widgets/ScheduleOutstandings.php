<?php

namespace App\Filament\Support\Widgets;

use App\Models\Outstanding;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ScheduleOutstandings extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Outstanding::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),
            ]);
    }
}
