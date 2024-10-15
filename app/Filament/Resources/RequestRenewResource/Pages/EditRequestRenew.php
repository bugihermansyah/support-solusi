<?php

namespace App\Filament\Resources\RequestRenewResource\Pages;

use App\Filament\Resources\RequestRenewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequestRenew extends EditRecord
{
    protected static string $resource = RequestRenewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
