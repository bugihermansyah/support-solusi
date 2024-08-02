<?php

namespace App\Listeners;

use App\Events\ClientMailEvent;
use App\Mail\ClientMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class ClientMailListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ClientMailEvent $event)
    {
        // $reporting = $event->reporting;

        Mail::to($event->toEmails)
            ->cc($event->ccEmails)
            ->send(new ClientMail(
                $event->reporting,
                $event->locationName,
                $event->outstandingNumber,
                $event->outstandingTitle,
                $event->outstandingReporter,
                $event->supportNames
            ));

        if (is_null($event->reporting->send_mail_at)) {

            $event->reporting->update(['send_mail_at' => now()]);
        }
    }
}
