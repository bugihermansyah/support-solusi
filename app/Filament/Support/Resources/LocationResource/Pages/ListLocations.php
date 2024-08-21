<?php

namespace App\Filament\Support\Resources\LocationResource\Pages;

use App\Filament\Support\Resources\LocationResource;
use App\Models\Location;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListLocations extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return LocationResource::getWidgets();
    }

    public function getTabs(): array
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;
        return [
            null => Tab::make('All')->query(fn ($query) => $query->where('team_id', $userTeam)),
            'My Locations' => Tab::make()->query(fn ($query) => $query->where('user_id', $user->id)),
        ];
    }
}
