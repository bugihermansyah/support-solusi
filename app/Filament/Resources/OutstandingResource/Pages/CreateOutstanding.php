<?php

namespace App\Filament\Resources\OutstandingResource\Pages;

use App\Filament\Resources\OutstandingResource;
use App\Filament\Support\Resources\OutstandingResource as SupportOutstandingResource;
use App\Models\Reporting;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOutstanding extends CreateRecord
{
    protected static string $resource = OutstandingResource::class;

    protected function afterCreate(): void
    {
        $outstanding = $this->record;
        $userLocation = $outstanding->location?->user_id;
        $userSchedule = $outstanding?->user_id;

        $sendUserLocation = User::find($userLocation);
        $sendUserSchedule = User::find($userSchedule);

        Reporting::create([
            'outstanding_id' => $outstanding->id,
            'date_visit' => $outstanding->date_visit,
            'user_id' => $outstanding->user_id,
            'status' => null,
        ]);

        Notification::make()
            ->title('Outstanding lokasi')
            ->icon('heroicon-o-inbox-arrow-down')
            ->body("<b>{$outstanding->location?->name} - {$outstanding?->title}</b>")
            ->actions([
                Action::make('Lihat')
                    ->url(SupportOutstandingResource::getUrl('edit', ['record' => $outstanding], panel: 'support')),
            ])
            ->sendToDatabase($sendUserLocation);

        Notification::make()
            ->title('Jadwal outstanding')
            ->icon('heroicon-o-inbox-arrow-down')
            ->body("<b>{$outstanding->location?->name} - {$outstanding?->title}</b>")
            ->actions([
                Action::make('Lihat')
                    ->url(SupportOutstandingResource::getUrl('edit', ['record' => $outstanding], panel: 'support')),
            ])
            ->sendToDatabase($sendUserSchedule);
    }
}
