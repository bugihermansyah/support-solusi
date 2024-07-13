<?php

namespace App\Filament\Support\Resources\LocationResource\Widgets;

use App\Filament\Support\Resources\LocationResource\Pages\ListLocations;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class LocationsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListLocations::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total lokasi anda', $this->getPageTableQuery()->count()),
            // Stat::make('Total produk unik', function () {
            //     return DB::table($this->getPageTableQuery())
            //         ->select(DB::raw('COUNT(DISTINCT product) as total'))
            //         ->groupBy('contract_location')
            //         ->count();
            // }),
        ];
    }
}
