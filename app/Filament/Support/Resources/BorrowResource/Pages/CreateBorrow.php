<?php

namespace App\Filament\Support\Resources\BorrowResource\Pages;

use App\Filament\Resources\Warehouse\LoanResource;
use App\Filament\Support\Resources\BorrowResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBorrow extends CreateRecord
{
    protected static string $resource = BorrowResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $request = $this->record;

        $user = auth()->user();
        $userAdmin = User::where('email', 'support@ptsap.co.id')->first();

        if ($userAdmin) {
            Notification::make()
                ->title("{$user->firstname} {$user->lastname}")
                ->icon('heroicon-o-cpu-chip')
                ->body("Permintaan unit dengan No. <b>{$request->number}</b>")
                ->actions([
                    Action::make('Lihat')
                        ->url(LoanResource::getUrl('edit', ['record' => $request], panel: 'admin')),
                ])
                ->sendToDatabase($userAdmin);
        }
    }

}
