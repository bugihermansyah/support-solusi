<?php

namespace App\Filament\Resources\Warehouse\RestockResource\Pages;

use App\Filament\Resources\Warehouse\RestockResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageRestocks extends ManageRecords
{
    protected static string $resource = RestockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();

                    return $data;
                })
                ->using(function (array $data, string $model): Model {
                    return $model::create($data);
                })
        ];
    }
}
