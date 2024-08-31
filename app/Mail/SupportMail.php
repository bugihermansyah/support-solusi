<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    // public $attachments;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $company = $this->mailData['company'] ?? '';
        $location = $this->mailData['location'] ?? '';
        $title = $this->mailData['title'] ?? '';

        $subject = "[Reporting] {$company} - {$location} : {$title}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function build()
    {
        $mail = $this->view('emails.reportMail')
                    ->with('data', $this->mailData)
                    ->subject('New Report Created');

        foreach ($this->mailData['attachments'] as $attachment) {
            $mail->attach($attachment);
        }

        return $mail;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            // Attachment::fromStorage($this->mailData['path'])
        ];
    }
}
