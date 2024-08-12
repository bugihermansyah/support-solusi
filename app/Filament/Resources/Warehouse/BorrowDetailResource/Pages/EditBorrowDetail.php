<?php

namespace App\Filament\Resources\Warehouse\BorrowDetailResource\Pages;

use App\Filament\Resources\Warehouse\BorrowDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBorrowDetail extends EditRecord
{
    protected static string $resource = BorrowDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
