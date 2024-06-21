<?php

namespace App\Filament\Resources\DisciplineResource\Pages;

use App\Filament\Resources\DisciplineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDisciplines extends ListRecords
{
    protected static string $resource = DisciplineResource::class;

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return parent::getTableQuery()->whereNotNull('team_id');
        }

        if ($user->hasRole(['head', 'staff'])) {
            return parent::getTableQuery()->where('team_id', $user->team_id);
        }

        return parent::getTableQuery();
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
