<?php

namespace App\Filament\Resources\RequestRenewResource\Pages;

use App\Filament\Resources\RequestRenewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequestRenews extends ListRecords
{
    protected static string $resource = RequestRenewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
