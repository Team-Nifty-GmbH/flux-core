<?php

namespace FluxErp\Listeners;

use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Livewire\Forms\CommunicationForm;
use FluxErp\Models\Communication;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;

class MessageSendingEventSubscriber
{
    public function handle(MessageSending $event): void
    {
        /** @var CommunicationForm $communicationForm */
        if (! $communicationForm = data_get($event, 'data.mailMessageForm')) {
            return;
        }

        if (! $communicationForm instanceof CommunicationForm || ! $communicationForm->communicatables
        ) {
            return;
        }

        // TODO: if a id is set for the attachment an existing media is being attached, create a relation between the communication and the media
        $communicationForm->attachments = array_filter(
            $communicationForm->attachments,
            fn ($attachment) => ! data_get($attachment, 'id')
        );

        $communicationForm->attachments = array_filter(
            array_map(
                function ($attachment) {
                    $attachment['model_type'] = morph_alias(Communication::class);
                    $attachment['collection_name'] = 'attachments';
                    $attachment['media_type'] = 'string';
                    $attachment['media'] = data_get($attachment, 'path');

                    return $attachment;
                },
                $communicationForm->attachments
            ),
            fn ($attachment) => ! is_null(data_get($attachment, 'media'))
        );

        $communicationForm->communication_type_enum = CommunicationTypeEnum::Mail->value;
        $communicationForm->save();

        $communication = resolve_static(Communication::class, 'query')
            ->whereKey($communicationForm->id)
            ->first();

        foreach ($communicationForm->communicatables as $communicatable) {
            $communicatableModel = resolve_static(data_get($communicatable, 'communicatable_type'), 'query')
                ->whereKey(data_get($communicatable, 'communicatable_type'))
                ->first();

            // if the given morph has a contact id always attach to the contact
            if ($communicatableModel->contact_id) {
                $communication->contacts()->syncWithoutDetaching($communicatable->contact_id);
            }
        }

        // add a tracking pixel
        $this->injectTracker($event->message, $communication);
    }

    public function subscribe(): array
    {
        return [
            MessageSending::class => 'handle',
        ];
    }

    protected function injectTracker(Email $email, Communication $communication): void
    {
        $html = $email->getHtmlBody();
        $text = $email->getTextBody();

        if ($html) {
            $email->html($this->injectTrackingPixel($html, $communication));
        }

        $email->text($text . "\n\n" . '[' . $communication->uuid . ']');
    }

    protected function injectTrackingPixel($html, Communication $communication): array|string
    {
        $tracking_pixel =
            '<img border=0 width=1 alt="" height=1 src="' . route('mail-pixel', [$communication->uuid]) . '" />';

        $linebreak = app(Str::class)->random(32);
        $html = str_replace("\n", $linebreak, $html);

        if (preg_match('/^(.*<body[^>]*>)(.*)$/', $html, $matches)) {
            $html = $matches[1] . $matches[2] . $tracking_pixel;
        } else {
            $html = $html . $tracking_pixel;
        }

        return str_replace($linebreak, "\n", $html);
    }
}
