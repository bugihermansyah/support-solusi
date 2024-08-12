<?php

namespace App\Filament\Resources\Warehouse\ReturnDetailResource\Pages;

use App\Filament\Resources\Warehouse\ReturnDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnDetails extends ListRecords
{
    protected static string $resource = ReturnDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
