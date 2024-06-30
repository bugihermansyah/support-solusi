<?php

namespace App\Filament\Resources\OutstandingResource\Pages;

use App\Filament\Resources\OutstandingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListOutstandings extends ListRecords
{
    protected static string $resource = OutstandingResource::class;

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole(['head', 'staff'])) {
            return parent::getTableQuery()
                ->join('locations', 'outstandings.location_id', '=', 'locations.id')
                ->join('teams', 'locations.team_id', '=', 'teams.id')
                ->where('teams.id', $user->team_id)
                ->select('outstandings.*');
        }

        return parent::getTableQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
