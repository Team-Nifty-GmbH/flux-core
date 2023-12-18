<?php

namespace FluxErp\Mail;

use FluxErp\Livewire\Forms\MailMessageForm;
use FluxErp\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MailMessageForm $mailMessageForm)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailMessageForm->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: $this->mailMessageForm->text_body,
            markdown: 'flux::emails.generic',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return array_filter(array_map(function ($attachment) {
            if (is_array($attachment) && ($attachment['path'] ?? false)) {
                return Attachment::fromPath($attachment['path'])->as($attachment['name'] ?? null);
            }

            if (is_a($attachment, UploadedFile::class)) {
                return Attachment::fromPath($attachment->getRealPath())->as($attachment->getClientOriginalName());
            }

            if (is_array($attachment) && ($attachment['id'] ?? false)) {
                return Media::query()->whereKey($attachment['id'])->first();
            }

            if (is_int($attachment)) {
                return Media::query()->whereKey($attachment)->first();
            }

            return $attachment;
        }, $this->mailMessageForm->attachments));
    }
}
