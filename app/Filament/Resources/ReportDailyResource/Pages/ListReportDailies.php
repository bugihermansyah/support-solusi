<?php

namespace App\Filament\Resources\ReportDailyResource\Pages;

use App\Filament\Resources\ReportDailyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListReportDailies extends ListRecords
{
    protected static string $resource = ReportDailyResource::class;

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;

        if ($user->hasRole(['head'])) {
            return parent::getTableQuery()
                ->join('outstandings', 'outstandings.id', '=', 'reportings.outstanding_id')
                ->join('locations', 'outstandings.location_id', '=', 'locations.id')
                ->where('locations.team_id', $userTeam)
                ->whereNotNull('reportings.status')
                ->select('reportings.*');
        }

        return parent::getTableQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
