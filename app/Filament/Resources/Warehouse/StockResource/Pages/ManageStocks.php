<?php

namespace App\Filament\Resources\Warehouse\StockResource\Pages;

use App\Filament\Resources\Warehouse\StockResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStocks extends ManageRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
