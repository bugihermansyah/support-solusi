<?php

namespace App\Filament\Resources\EmailAdressResource\Pages;

use App\Filament\Resources\EmailAdressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailAdress extends EditRecord
{
    protected static string $resource = EmailAdressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
