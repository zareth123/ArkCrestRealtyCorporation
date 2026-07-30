<?php

namespace App\Mail;

use App\Models\PropertyInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PropertyInquiry $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Website Inquiry — ' . $this->inquiry->full_name,
            // Lets ArkCrest staff hit "Reply" in Gmail and answer the client directly.
            replyTo: [new Address($this->inquiry->email, $this->inquiry->full_name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.property-inquiry');
    }
}