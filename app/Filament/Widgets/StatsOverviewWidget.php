<?php

namespace App\Filament\Widgets;

// use Filament\Widgets\Concerns\InteractsWithPageFilters;
// use Filament\Widgets\StatsOverviewWidget as BaseWidget;
// use Filament\Widgets\StatsOverviewWidget\Stat;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
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

        // $openOutstanding = $outstandingQuery->count();

        $latestStatuses = DB::table('reportings as r1')
            ->select('r1.outstanding_id', 'r1.status')
            ->whereRaw('r1.created_at = (SELECT MAX(r2.created_at) FROM reportings r2 WHERE r2.outstanding_id = r1.outstanding_id)');

        // Gabungkan ke outstandings dan locations agar bisa filter berdasarkan team_id
        $countsQuery = DB::table(DB::raw("({$latestStatuses->toSql()}) as latest"))
            ->mergeBindings($latestStatuses)
            ->join('outstandings', 'outstandings.id', '=', 'latest.outstanding_id')
            ->join('locations', 'locations.id', '=', 'outstandings.location_id')
            ->where('outstandings.status', 0)
            ->where('outstandings.date_in', '<=', now()->subDays(3))
            ->where('outstandings.reporter', '!=', 'preventif')
            ->selectRaw("
                SUM(CASE WHEN latest.status = 0 THEN 1 ELSE 0 END) AS pending_sap,
                SUM(CASE WHEN latest.status = 2 THEN 1 ELSE 0 END) AS pending_client,
                SUM(CASE WHEN latest.status = 3 THEN 1 ELSE 0 END) AS temporary,
                SUM(CASE WHEN latest.status = 4 THEN 1 ELSE 0 END) AS monitoring
            ");

        // Tambahkan kondisi filter berdasarkan team user
        if ($user->team_id && !$user->hasRole('admin')) {
            $countsQuery->where('locations.team_id', $user->team_id);
        }

        $counts = $countsQuery->first();


        return [
            Stat::make('Location', $totalLocations)
                ->icon('heroicon-o-map-pin'),
            Stat::make('Pending SAP', $counts->pending_sap ?? 0)
                ->icon('heroicon-o-clock'),
            Stat::make('Pending Client', $counts->pending_client ?? 0)
                ->icon('heroicon-o-user-group'),
            Stat::make('Temporary', $counts->temporary ?? 0)
                ->icon('heroicon-o-wrench')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50',
                    'onclick' => "window.open('/admin/reportings?status=temporary', '_blank')",
                ]),
            Stat::make('Monitoring', $counts->monitoring ?? 0)
                ->icon('heroicon-o-eye')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50',
                    'onclick' => "window.open('/admin/reportings?status=monitoring', '_blank')",
                ]),
        ];
    }
}
