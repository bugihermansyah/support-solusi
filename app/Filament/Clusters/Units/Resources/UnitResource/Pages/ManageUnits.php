<?php

namespace App\Filament\Clusters\Units\Resources\UnitResource\Pages;

use App\Filament\Clusters\Units\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageUnits extends ManageRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
                // ->modalWidth(MaxWidth::ExtraSmall),
        ];
    }
}
