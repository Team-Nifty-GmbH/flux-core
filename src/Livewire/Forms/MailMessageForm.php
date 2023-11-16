<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Models\MailMessage;
use Livewire\Form;

class MailMessageForm extends Form
{
    public ?int $id = null;

    public ?string $from = null;

    public array $to = [];

    public array $cc = [];

    public array $bcc = [];

    public ?string $date = null;

    public ?string $subject = null;

    public ?string $html_body = null;

    public ?string $text_body = null;

    public array $attachments = [];

    public ?string $slug = null;

    public function fill($values)
    {
        parent::fill($values);

        if ($this->id) {
            $message = $values instanceof MailMessage
                ? $values->load(['mailFolder:id,slug', 'mailAccount:id,email'])
                : MailMessage::query()
                    ->whereKey($this->id)
                    ->with(['mailFolder:id,slug', 'mailAccount:id,email'])
                    ->first();

            $this->attachments = $message
                ->getMedia('attachments')
                ->map(fn ($media) => [
                    'id' => $media->id,
                    'name' => $media->name,
                ])
                ->toArray();

            $this->slug = $message->mailAccount->email . ' -> ' . $message->mailFolder?->slug;
        }
    }
}
