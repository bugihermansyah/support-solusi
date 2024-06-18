<?php

namespace App\Filament\Resources\OutstandingResource\Pages;

use App\Filament\Resources\OutstandingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutstandings extends ListRecords
{
    protected static string $resource = OutstandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
