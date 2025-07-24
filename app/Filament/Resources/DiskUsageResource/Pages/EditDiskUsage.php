<?php

namespace App\Filament\Resources\DiskUsageResource\Pages;

use App\Filament\Resources\DiskUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiskUsage extends EditRecord
{
    protected static string $resource = DiskUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
