<?php

namespace App\Filament\Resources\Warehouse\BorrowDetailResource\Pages;

use App\Filament\Resources\Warehouse\BorrowDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListBorrowDetails extends ListRecords
{
    protected static string $resource = BorrowDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
