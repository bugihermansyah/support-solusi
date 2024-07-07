<?php

namespace App\Filament\Resources\QuickOutstandingResource\Pages;

use App\Filament\Resources\QuickOutstandingResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageQuickOutstandings extends ManageRecords
{
    protected static string $resource = QuickOutstandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->extraModalFooterActions(fn (Action $action): array => [
                    $action->makeModalSubmitAction('sendEmailAction', ['sendEmailArgument' => true])
                        ->label('Buat & Kirim email')
                ]),
        ];
    }
}
