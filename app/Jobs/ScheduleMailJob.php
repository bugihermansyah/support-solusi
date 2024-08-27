<?php

namespace App\Jobs;

use App\Mail\ScheduleMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ScheduleMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $emails;
    protected string $dateVisit;
    protected string $companyAlias;
    protected string $locationName;
    protected string $title;

    /**
     * Create a new job instance.
     */
    public function __construct(array $emails, string $dateVisit, string $companyAlias, string $locationName, string $title)
    {
        $this->emails = $emails;
        $this->dateVisit = $dateVisit;
        $this->companyAlias = $companyAlias;
        $this->locationName = $locationName;
        $this->title = $title;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Mail::to($this->emails)->send(new ScheduleMail(
            $this->dateVisit,
            $this->companyAlias,
            $this->locationName,
            $this->title
        ));
    }
}
