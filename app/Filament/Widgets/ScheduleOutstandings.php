<?php

namespace App\Filament\Widgets;

use App\Models\Reporting;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ScheduleOutstandings extends BaseWidget
{
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(
                Reporting::query()
                    ->where('status', null)
            )
            ->columns([
                TextColumn::make('date_visit')
                    ->label('Tanggal')
                    ->date(),
                TextColumn::make('user.firstname')
                    ->label('Support'),
                TextColumn::make('outstanding.location.name')
                    ->label('Lokasi'),
                TextColumn::make('outstanding.title')
                    ->label('Masalah'),
            ]);
    }
}
