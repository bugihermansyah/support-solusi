<?php

namespace App\Filament\Resources\ReportDailyResource\Pages;

use App\Filament\Resources\ReportDailyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportDailies extends ListRecords
{
    protected static string $resource = ReportDailyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
