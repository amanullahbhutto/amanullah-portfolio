<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address') ?: 'amanullah@triplewtools.com';
        $fromName = config('mail.from.name') ?: 'Amanullah Portfolio';

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
            replyTo: [$this->contactMessage->email],
            subject: 'New portfolio contact: '.$this->contactMessage->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message-received',
        );
    }
}
