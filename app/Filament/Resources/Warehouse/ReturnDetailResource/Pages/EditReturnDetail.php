<?php

namespace App\Filament\Resources\Warehouse\ReturnDetailResource\Pages;

use App\Filament\Resources\Warehouse\ReturnDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturnDetail extends EditRecord
{
    protected static string $resource = ReturnDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
