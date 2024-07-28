<?php

namespace App\Filament\Pages\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TotalForReportOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $userTeam = Auth::user()->team_id;

        $totalLocation = DB::table('locations')
                            ->where('team_id', $userTeam);

        $totalLocationMasalah = DB::table('outstandings')
                                    ->join('locations', 'locations.id', '=', 'outstandings.location_id')
                                    ->groupBy('locations.id')
                                    ->groupBy('outstandings.date_in')
                                    ->select('locations.name')
                                    // ->get()
                                    ->count();

        $totalSlaVisit = DB::table('outstandings')
                            ->select(DB::raw('COUNT(IF(DATEDIFF(date_visit, date_in) <= 1, 1, NULL)) AS sla_1'))
                            ->value('sla_1');

        $totalAksi = DB::table('reportings');

        $totalMasalah = DB::table('outstandings')
                            ->join('locations', 'locations.id', '=', 'outstandings.location_id')
                            ->where('locations.team_id', $userTeam);


        return [
            Stat::make('Lokasi by team', $totalLocation->count())
                ->description($totalLocationMasalah.' total lokasi masalah')
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
            Stat::make('Total masalah', $totalMasalah->count())
                ->description('L.P.M '.$totalMasalah->where('lpm', 1)->count())
                // ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('SLA visit', $totalSlaVisit)
                // ->description('3% increase')
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
            Stat::make('Aksi', $totalAksi->count())
                ->description('Visit '.$totalAksi->where('work', 'visit')->count().' | '.$totalAksi->where('work', 'Remote')->count().' Remote')
                // ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
