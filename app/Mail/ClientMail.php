<?php

namespace App\Mail;

use App\Models\Reporting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reporting;
    public $locationName;
    public $outstandingNumber;
    public $outstandingTitle;
    public $outstandingReporter;
    public $supportNames;

    public function __construct(
        Reporting $reporting,
        string $locationName,
        string $outstandingNumber,
        string $outstandingTitle,
        string $outstandingReporter,
        array $supportNames
        )
    {
        $this->reporting = $reporting;
        $this->locationName = $locationName;
        $this->outstandingNumber = $outstandingNumber;
        $this->outstandingTitle = $outstandingTitle;
        $this->outstandingReporter = $outstandingReporter;
        $this->supportNames = $supportNames;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $location = $this->locationName ?? '';
        $title = $this->outstandingTitle ?? '';

        $subject = "{$location} - {$title}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function build()
    {
        $email = $this->from('noreply.support@ptsap.co.id', 'Support | PT SAP')
                      ->view('emails.reportingClientNotification')
                      ->with([
                          'reporting' => $this->reporting,
                          'locationName' => $this->locationName,
                          'outstandingNumber' => $this->outstandingNumber,
                          'outstandingTitle' => $this->outstandingTitle,
                          'outstandingReporter' => $this->outstandingReporter,
                          'supportNames' => $this->supportNames,
                        ]);

        // Add attachments
        foreach ($this->reporting->getMedia('default') as $media) {
            $email->attach($media->getPath(), [
                'as' => $media->file_name,
                'mime' => $media->mime_type,
            ]);
        }

        return $email;
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
