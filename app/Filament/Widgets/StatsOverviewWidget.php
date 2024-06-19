<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $typeContract = $this->filters['typeContract'] ?? 'all';
        $selectedMonth = $this->filters['month'] ?? null;
        $selectedYear = $this->filters['year'] ?? null;

            // Query to get the total number of locations based on the type_contract filter
        $query = DB::table('locations');

        if ($typeContract !== 'all') {
            $query->where('type_contract', $typeContract);
        }

        if ($selectedMonth) {
            $query->whereMonth('created_at', $selectedMonth);
        }

        if ($selectedYear) {
            $query->whereYear('created_at', $selectedYear);
        }

        $totalLocations = $query->count();

        $areaLocations = DB::table('locations')->where('area_status', 'out')->count();

        $outstandingAll = DB::table('outstandings')->where('status', 0)->count();

        $outstandingQuery = DB::table('outstandings')->where('status', 0);

        if ($selectedMonth) {
            $outstandingQuery->whereMonth('created_at', $selectedMonth);
        }

        if ($selectedYear) {
            $outstandingQuery->whereYear('created_at', $selectedYear);
        }

        $openOutstanding = $outstandingQuery->count();

        return [
            Stat::make('Total Lokasi', $totalLocations)
                ->description('Jumlah lokasi tim area')
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                // ->chart([10, 20, 30, 40, 50, 60, 70])
                ->color('primary'),
            Stat::make('Total Outstanding', $outstandingAll)
                ->description('Total outstanding yang belum selesai')
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                // ->chart([10, 20, 30, 40, 50, 60, 70])
                ->color('primary'),
            Stat::make('Outstanding', $openOutstanding)
                ->description('Outstanding berdasarkan filter')
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                // ->chart([10, 20, 30, 40, 50, 60, 70])
                ->color('primary'),
            Stat::make('Total Locations', $areaLocations)
                ->description('Lokasi luar kota')
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                // ->chart([10, 20, 30, 40, 50, 60, 70])
                ->color('primary'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
