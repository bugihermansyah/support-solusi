<?php

namespace App\Filament\Support\Resources\OutstandingResource\Pages;

use App\Filament\Support\Resources\OutstandingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOutstanding extends CreateRecord
{
    protected static string $resource = OutstandingResource::class;
}
