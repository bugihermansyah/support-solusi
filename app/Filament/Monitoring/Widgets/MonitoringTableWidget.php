<?php

namespace App\Filament\Monitoring\Widgets;

use App\Models\Outstanding;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use EightyNine\FilamentAdvancedWidget\AdvancedTableWidget as BaseWidget;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class MonitoringTableWidget extends BaseWidget
{
    public function table(Table $table): Table
    {
        $query = Outstanding::query()
                    ->where('outstandings.status', 0)
                    ->whereNot('is_implement', 1)
                    ->orderByRaw("
                    CASE
                        WHEN priority = 'high' THEN 1
                        WHEN priority = 'normal' THEN 2
                        WHEN priority = 'low' THEN 3
                        ELSE 4
                    END
                    ");
        return $table
            // ->defaultPaginationPageOption(5)
            ->query($query->select('outstandings.*'))
            ->defaultSort('date_in', 'asc')
            ->defaultSort('priority', 'asc')
            ->poll('30s')
            ->deferLoading()
            ->columns([
                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->limit(15)
                    ->badge()
                    ->color(function ($record): string {
                        $createdAt = Carbon::parse($record->date_in);
                        $daysDifference = $createdAt->diffInDays(Carbon::now());

                        if ($daysDifference < 3) {
                            return 'gray';
                        } elseif ($daysDifference <= 7) {
                            return 'warning';
                        } else {
                            return 'danger';
                        }
                    }),
                TextColumn::make('title')
                    ->label('Masalah')
                    ->badge()
                    ->color(function ($record): string {
                        $createdAt = Carbon::parse($record->date_in);
                        $daysDifference = $createdAt->diffInDays(Carbon::now());

                        if ($daysDifference < 3) {
                            return 'gray';
                        } elseif ($daysDifference <= 7) {
                            return 'warning';
                        } else {
                            return 'danger';
                        }
                    }),
                TextColumn::make('date_in')
                    ->label('Sejak')
                    ->since()
                    ->badge()
                    ->color(function (string $state): string {
                        $createdAt = Carbon::parse($state);
                        $daysDifference = $createdAt->diffInDays(Carbon::now());

                        if ($daysDifference < 3) {
                            return 'gray';
                        } elseif ($daysDifference <= 7) {
                            return 'warning';
                        } else {
                            return 'danger';
                        }
                    }),
                IconColumn::make('temporary')
                    ->label('Temp')
                    ->getStateUsing(function (Outstanding $record){
                        return $record->date_finish ? true : false;
                    })
                    ->boolean(),
            ]);
            // ->actions([
            //     Action::make('edit')
            //         ->icon('heroicon-m-eye')
            //         ->hiddenLabel()
            //         ->url(fn (Outstanding $record): string => route('filament.admin.resources.outstandings.edit', $record->id))
            //         ->openUrlInNewTab(),
            // ]);
    }
}
