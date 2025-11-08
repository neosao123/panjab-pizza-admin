<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExcelAttachmentEmail extends Mailable
{
    use Queueable, SerializesModels;


    public function envelope()
    {
        return new Envelope(
            subject: 'Store Location Wise Reports -' . date('Y-m-d'),
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.store_reports',
            with: ['name' => "Abhi Harshe"],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $now = date('Y-m-d');
        $excelFilePath3 = storage_path('app/public/STR_3_' . $now . '.xlsx');
        $excelFilePath1 = storage_path('app/public/STR_1_' . $now . '.xlsx');
        $excelFilePath2 = storage_path('app/public/STR_2_' . $now . '.xlsx');
        return [
            Attachment::fromPath($excelFilePath1),
            Attachment::fromPath($excelFilePath2),
            Attachment::fromPath($excelFilePath3),
        ];
    }
}
