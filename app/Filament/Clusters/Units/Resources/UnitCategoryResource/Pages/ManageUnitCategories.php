<?php

namespace App\Filament\Clusters\Units\Resources\UnitCategoryResource\Pages;

use App\Filament\Clusters\Units\Resources\UnitCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageUnitCategories extends ManageRecords
{
    protected static string $resource = UnitCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->modalWidth(MaxWidth::Small),
        ];
    }
}
