<?php

namespace FluxErp\Mail;

use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MagicLoginLink extends Mailable
{
    use Makeable, Queueable;

    public function __construct(private readonly string $url) {}

    public function content(): Content
    {
        return new Content(
            markdown: 'flux::emails.magic-login-link',
            with: [
                'url' => $this->url,
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' ' . __('Login Link')
        );
    }
}
