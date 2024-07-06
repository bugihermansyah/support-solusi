<?php

namespace App\Filament\Support\Resources\OutstandingResource\Pages;

use App\Filament\Support\Resources\OutstandingResource;
use App\Models\Outstanding;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListOutstandings extends ListRecords
{
    protected static string $resource = OutstandingResource::class;

    protected function getTableQuery(): Builder
    {
        // Get the logged-in user
        $user = Auth::user();

        // Return the query to fetch outstandings related to the logged-in user's locations
        return Outstanding::whereHas('location', function (Builder $query) use ($user) {
            $query->where('user_id', $user->id);
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
