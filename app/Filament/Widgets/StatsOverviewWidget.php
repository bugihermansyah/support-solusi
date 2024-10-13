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
        $user = auth()->user();

        // Initialize the query for locations
        $locationQuery = DB::table('locations');

        // Apply team filter if the user has a team and is not an admin
        if ($user->team_id && !$user->hasRole('admin')) {
            $locationQuery->where('team_id', $user->team_id);
        }

        $totalLocations = $locationQuery->count();

        // Get the count of locations with 'area_status' as 'out'
        // $areaLocations = $locationQuery->clone()->where('area_status', 'out')->count();

        // Initialize the query for outstandings
        $outstandingQuery = DB::table('outstandings')
            ->join('locations', 'outstandings.location_id', '=', 'locations.id')
            ->where('outstandings.status', 0);

        // Apply team filter for outstandings based on user's team and location
        if ($user->team_id && !$user->hasRole('admin')) {
            $outstandingQuery->where('locations.team_id', $user->team_id);
        }

        $openOutstanding = $outstandingQuery->count();

        return [
            Stat::make('Lokasi', $totalLocations)
                ->description('Jumlah lokasi tim area')
                ->color('primary'),
            Stat::make('Outstanding', $openOutstanding)
                ->description('Outstanding berdasarkan filter')
                ->color('primary'),
            // Stat::make('Luar kota', $areaLocations)
            //     ->description('Reporting luar kota')
            //     ->color('primary'),
        ];
    }
}
