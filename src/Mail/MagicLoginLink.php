<?php

namespace FluxErp\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class MagicLoginLink extends Mailable
{
    use Queueable, SerializesModels;

    private string $plaintextToken;

    private Carbon $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct(string $plaintextToken, Carbon $expiresAt)
    {
        $this->plaintextToken = $plaintextToken;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name') . ' ' . __('Login Link')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'flux::emails.magic-login-link',
            with: [
                'url' => URL::temporarySignedRoute('login-link', $this->expiresAt, [
                    'token' => $this->plaintextToken,
                ]),
            ],
        );
    }
}
