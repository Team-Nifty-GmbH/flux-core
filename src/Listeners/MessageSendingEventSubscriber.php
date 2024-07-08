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

        if (! $communicationForm instanceof CommunicationForm
            || ! $communicationForm->communicatable_type
            || ! $communicationForm->communicatable_id
        ) {
            return;
        }

        // TODO: if a id is set for the attachment an existing media is being attached, create a relation between the communication and the media
        $communicationForm->attachments = array_filter(
            $communicationForm->attachments,
            fn ($attachment) => ! data_get($attachment, 'id')
        );
        $communicationForm->attachments = array_map(
            function ($attachment) {
                $attachment['model_type'] = Communication::class;
                $attachment['collection_name'] = 'attachments';
                $attachment['media_type'] = 'string';
                $attachment['media'] = $attachment['path'];

                return $attachment;
            },
            $communicationForm->attachments
        );
        $communicationForm->communication_type_enum = CommunicationTypeEnum::Mail->value;
        $communicationForm->save();

        $communication = app(Communication::class)->query()->whereKey($communicationForm->id)->first();

        $communicatable = Relation::getMorphedModel($communicationForm->communicatable_type)::query()
            ->whereKey($communicationForm->communicatable_id)
            ->first();

        // if the given morph has a contact id always attach to the contact
        if ($communicatable->contact_id) {
            $communication->contacts()->syncWithoutDetaching($communicatable->contact_id);
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

    protected function injectTracker(Email $email, Communication $communication): void
    {
        $html = $email->getHtmlBody();
        $text = $email->getTextBody();

        if ($html) {
            $email->html($this->injectTrackingPixel($html, $communication));
        }

        $email->text($text . "\n\n" . '[' . $communication->uuid . ']');
    }
}
