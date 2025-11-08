<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailyStoreSummmary extends Mailable
{
    use Queueable, SerializesModels;

    public $records;
    public $date;

    /**
     * Create a new message instance.
     *
     * @param array $formData
     * @return void
     */
    public function __construct($records, $date)
    {
        $this->records = $records;
        $this->date = $date;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'All stores summary for date #' . $this->date,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.store-summary-daily',
            with: ['date' => $this->date, 'records' => $this->records],
        );
    }
}
