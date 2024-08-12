<?php

namespace App\Filament\Support\Resources\BorrowResource\Pages;

use App\Filament\Support\Resources\BorrowResource;
use App\Models\Loan;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBorrows extends ListRecords
{
    protected static string $resource = BorrowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        return Loan::where('user_id', $user->id);
    }
}
