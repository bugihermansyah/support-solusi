<?php

namespace App\Filament\Resources\ReportDailyResource\Pages;

use App\Filament\Resources\ReportDailyResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditReportDaily extends EditRecord
{
    protected static string $resource = ReportDailyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
            Action::make('Send Notifications')
                ->icon('heroicon-m-bell')
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
