<?php

namespace App\Filament\Support\Resources\LocationResource\Pages;

use App\Filament\Support\Resources\LocationResource;
use App\Models\Location;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        return Location::where('user_id', $user->id);

    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
