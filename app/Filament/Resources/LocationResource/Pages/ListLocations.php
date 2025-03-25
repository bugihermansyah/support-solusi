<?php

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole(['head', 'staff'])) {
            return parent::getTableQuery()->where('team_id', $user->team_id);
        }

        return parent::getTableQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'settle' => Tab::make()->query(fn ($query) => $query->where('status', 'settle')),
            'implementasi' => Tab::make()->query(fn ($query) => $query->where('status', 'implementasi')),
            'dismantle' => Tab::make()->query(fn ($query) => $query->where('status', 'dismantle')),
        ];
    }
}
