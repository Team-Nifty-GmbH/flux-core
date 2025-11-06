<?php

namespace FluxErp\Listeners;

use FluxErp\Actions\Communication\CreateCommunication;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Enums\CommunicationTypeEnum;
use FluxErp\Models\Communication;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;

class MessageSendingEventSubscriber
{
    public function handle(MessageSending $event): void
    {
        if (! $communicationForm = data_get($event, 'data.mailMessageForm')) {
            return;
        }

        $communicationForm = $communicationForm instanceof Arrayable
            ? $communicationForm->toArray()
            : $communicationForm;

        if (! is_array($communicationForm)) {
            return;
        }

        // TODO: if a id is set for the attachment an existing media is being attached, create a relation between the communication and the media
        $communicationForm['attachments'] = array_filter(
            data_get($communicationForm, 'attachments', []),
            fn ($attachment) => ! data_get($attachment, 'id')
        );

        $communicationForm['attachments'] = array_filter(
            array_map(
                function ($attachment) {
                    $attachment['model_type'] = morph_alias(Communication::class);
                    $attachment['collection_name'] = 'attachments';
                    $attachment['media_type'] = 'string';
                    $attachment['media'] = data_get($attachment, 'path');

                    return $attachment;
                },
                data_get($communicationForm, 'attachments', [])
            ),
            fn ($attachment) => ! is_null(data_get($attachment, 'media'))
        );

        $communicationForm['communication_type_enum'] = CommunicationTypeEnum::Mail;
        $communicationForm['subject'] = (string) data_get($communicationForm, 'subject');
        $communicationForm['text_body'] = (string) data_get($communicationForm, 'body');
        $communicationForm['html_body'] = (string) data_get($communicationForm, 'html_body');

        $communicationAction = data_get($communicationForm, 'id')
            ? UpdateCommunication::make($communicationForm)
            : CreateCommunication::make($communicationForm);

        $communication = $communicationAction
            ->validate()
            ->execute();

        foreach (data_get($communicationForm, 'communicatables', []) as $communicatable) {
            $communicatableModel = morphed_model(data_get($communicatable, 'communicatable_type'))::query()
                ->whereKey(data_get($communicatable, 'communicatable_id'))
                ->first();

            // if the given morph has a contact id always attach to the contact
            if ($communicatableModel?->contact_id) {
                $communication->contacts()->syncWithoutDetaching($communicatableModel->contact_id);
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
