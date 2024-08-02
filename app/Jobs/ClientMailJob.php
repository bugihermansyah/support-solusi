<?php

namespace App\Jobs;

use App\Mail\ClientMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ClientMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reporting;
    protected $toEmails;
    protected $ccEmails;
    protected $locationName;
    protected $outstandingNumber;
    protected $outstandingTitle;
    protected $outstandingReporter;
    protected $supportNames;

    /**
     * Create a new job instance.
     *
     * @param mixed $reporting
     * @param array $toEmails
     * @param array $ccEmails
     * @param string $locationName
     * @param string $outstandingNumber
     * @param string $outstandingTitle
     * @param string $outstandingReporter
     * @param array $supportNames
     */
    public function __construct($reporting, $toEmails, $ccEmails, $locationName, $outstandingNumber, $outstandingTitle, $outstandingReporter, $supportNames)
    {
        $this->reporting = $reporting;
        $this->toEmails = $toEmails;
        $this->ccEmails = $ccEmails;
        $this->locationName = $locationName;
        $this->outstandingNumber = $outstandingNumber;
        $this->outstandingTitle = $outstandingTitle;
        $this->outstandingReporter = $outstandingReporter;
        $this->supportNames = $supportNames;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->toEmails)
            ->cc($this->ccEmails)
            ->send(new ClientMail(
                $this->reporting,
                $this->locationName,
                $this->outstandingNumber,
                $this->outstandingTitle,
                $this->outstandingReporter,
                $this->supportNames
            ));

        if (is_null($this->reporting->send_mail_at)) {
            $this->reporting->update(['send_mail_at' => now()]);
        }
    }
}
