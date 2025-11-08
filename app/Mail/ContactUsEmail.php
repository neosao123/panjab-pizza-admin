<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactUsEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $formData;

    /**
     * Create a new message instance.
     *
     * @param array $formData
     * @return void
     */
    public function __construct($formData)
    {
        $this->formData = $formData;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'New Contact Us Form Submission - ' . $this->formData['firstName'] . ' ' .  $this->formData['lastName'],
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.contact_us',
            with: ['formdata' => $this->formData],
        );
    }
}
