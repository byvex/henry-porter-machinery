<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EnquiryReceived extends Mailable
{
    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Website enquiry: '.($this->enquiry->product_name ?? 'General enquiry'), replyTo: [new Address($this->enquiry->email, $this->enquiry->name)]);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.enquiry');
    }
}
