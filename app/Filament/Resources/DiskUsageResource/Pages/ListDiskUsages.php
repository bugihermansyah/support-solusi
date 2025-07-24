<?php

namespace App\Filament\Resources\DiskUsageResource\Pages;

use App\Filament\Resources\DiskUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiskUsages extends ListRecords
{
    protected static string $resource = DiskUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
