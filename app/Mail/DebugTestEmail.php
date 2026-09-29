<?php

namespace App\Mail;

use App\Models\VivoUsers;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DebugTestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public VivoUsers $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'SMTP delivery test');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.debug-test');
    }
}