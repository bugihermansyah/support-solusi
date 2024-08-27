<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $dateVisit;
    public string $companyAlias;
    public string $locationName;
    public string $title;

    /**
     * Create a new message instance.
     */
    public function __construct(string $dateVisit, string $companyAlias, string $locationName, string $title)
    {
        $this->dateVisit = $dateVisit;
        $this->companyAlias = $companyAlias;
        $this->locationName = $locationName;
        $this->title = $title;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $dateVisit = $this->dateVisit;
        $companyAlias = $this->companyAlias;
        $locationName = $this->locationName;
        $title = $this->title;

        $subjectMail = "[Jadwal {$dateVisit}] {$companyAlias} - {$locationName} : {$title}";
        return new Envelope(
            subject: $subjectMail,
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("[Jadwal {$this->dateVisit}] {$this->companyAlias} - {$this->locationName} : {$this->title}")
                    ->view('emails.scheduleMail');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
