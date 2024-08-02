<?php

namespace App\Filament\Resources\ReportDailyResource\Pages;

use App\Events\ClientMailEvent;
use App\Filament\Resources\ReportDailyResource;
use App\Models\Reporting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditReportDaily extends EditRecord
{
    protected static string $resource = ReportDailyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form'),
            Action::make('Send Notifications')
                ->icon('heroicon-m-bell')
                ->requiresConfirmation()
                ->action(function () {
                    $this->sendNotificationEmail($this->record);

                    Notification::make()
                    ->title('Notifikasi ke client berhasil')
                    ->success()
                    ->send();
                }),
        ];
    }

    protected function sendNotificationEmail(Reporting $reporting)
    {
        $reporting = $reporting->load('outstanding.location', 'users');
        $outstanding = $reporting->outstanding;

        if (!$outstanding) {
            Notification::make()
                ->title('Outstanding not found for this report.')
                ->danger()
                ->send();
            return;
        }

        $location = $outstanding->location;

        if (!$location) {
            Notification::make()
                ->title('Location not found for this outstanding.')
                ->danger()
                ->send();
            return;
        }

        $locationName = $location->name;
        $outstandingTitle = $outstanding->title;
        $outstandingReporter = $outstanding->reporter;
        $outstandingNumber = $outstanding->number;

        $toEmails = [];
        $ccEmails = [];
        $supportNames = [];

        foreach ($location->customers as $customer) {
            if ($customer->pivot->is_to) {
                $toEmails[] = $customer->email;
            } else {
                $ccEmails[] = $customer->email;
            }
        }

        foreach ($reporting->users as $user) {
            $supportNames[] = $user->firstname;
        }

        // dd($supportNames);
        // Trigger event with the collected emails on 'emailClient' queue
        ClientMailEvent::dispatch($reporting, $toEmails, $ccEmails, $locationName, $outstandingNumber, $outstandingTitle, $outstandingReporter, $supportNames);
            // ->onQueue('emailClients');
    }


    // protected function getFormActions(): array
    // {
    //     return [];
    // }
}
