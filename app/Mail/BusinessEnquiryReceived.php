<?php

namespace App\Mail;

use App\Models\BusinessEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BusinessEnquiryReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public BusinessEnquiry $enquiry,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New {$this->enquiry->partner_type->label()} Enquiry — {$this->enquiry->firm_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.business-enquiry-received',
        );
    }
}
