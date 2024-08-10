<?php

namespace App\Jobs;

use App\Mail\SupportMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SupportMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailTo;
    protected $mailCc;
    protected $mailData;

    /**
     * Create a new job instance.
     *
     * @param string $mailTo
     * @param array $mailCc
     * @param array $mailData
     * @return void
     */
    public function __construct($mailTo, $mailCc, $mailData)
    {
        $this->mailTo = $mailTo;
        $this->mailCc = $mailCc;
        $this->mailData = $mailData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->mailTo)
            ->cc($this->mailCc)
            ->send(new SupportMail($this->mailData));
    }
}
