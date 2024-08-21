<?php

namespace App\Filament\Support\Resources\OutstandingResource\Pages;

use App\Filament\Support\Resources\OutstandingResource;
use App\Models\Outstanding;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListOutstandings extends ListRecords
{
    protected static string $resource = OutstandingResource::class;

    // protected function getTableQuery(): Builder
    // {
    //     $user = Auth::user();

    //     return Outstanding::whereHas('location', function (Builder $query) use ($user) {
    //         $query->where('user_id', $user->id);
    //     });
    // }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();
        $userTeam = $user ? $user->getTeamId() : null ;
        return [
            null => Tab::make('All')
                ->query(function ($query) use ($userTeam) {
                    return $query->whereHas('location', function ($query) use ($userTeam) {
                        $query->where('team_id', $userTeam);
                    });
                }),

            'My Outstandings' => Tab::make('My Outstandings')
                ->query(function ($query) use ($user) {
                    return $query->whereHas('location', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                }),
        ];
    }
}
