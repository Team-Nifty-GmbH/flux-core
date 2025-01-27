<?php

namespace FluxErp\Listeners\MailMessage;

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Communication;
use FluxErp\Models\Currency;
use FluxErp\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\CauserResolver;

class CreateMailExecutedSubscriber
{
    protected ?Address $address = null;

    public function handle(CreateMailMessage $event): void
    {
        $message = $event->getResult();

        $this->address = resolve_static(
            Address::class,
            'findAddressByEmail',
            ['email' => Str::between($message->from, '<', '>')]
        );

        if ($this->address) {
            CauserResolver::setCauser($this->address);
        }

        if ($message->mailFolder->can_create_purchase_invoice && $message->media()->count() !== 0) {
            $this->createPurchaseInvoice($message);
        }

        $matches = [];
        preg_match(
            '/\[flux:comment:(\w+):(\d+)]/',
            $message->text_body . $message->html_body . $message->subject,
            $matches
        );
        if (count($matches) === 3 && $message->mailFolder->can_create_ticket) {
            $model = $matches[1];
            $id = $matches[2];

            try {
                CreateComment::make([
                    'model_type' => $model,
                    'model_id' => $id,
                    'comment' => $message->text_body ?? $message->html_body ?? $message->subject,
                    'is_internal' => false,
                ])->validate()->execute();
            } catch (\Throwable) {
            }
        } elseif ($message->mailFolder->can_create_ticket) {
            $this->createTicket($message);
        }
    }

    public function subscribe(): array
    {
        return [
            'action.executed: ' . resolve_static(CreateMailMessage::class, 'class') => 'handle',
        ];
    }

    protected function createPurchaseInvoice(Communication $message): void
    {
        $contact = $this->address?->contact;

        foreach ($message->getMedia('attachments') as $attachment) {
            try {
                CreatePurchaseInvoice::make([
                    'client_id' => $contact?->client_id ?? Client::default()->id,
                    'contact_id' => $contact?->id,
                    'currency_id' => $contact?->currency_id ?? Currency::default()->id,
                    'payment_type_id' => $contact?->purchase_payment_type_id ?? $contact?->payment_type_id,
                    'invoice_date' => $message->date->toDateString(),

                    'media' => [
                        'id' => $attachment->id,
                    ],
                ])->validate()->execute();
            } catch (\Throwable) {
            }
        }
    }

    protected function createTicket(Communication $communication): void
    {
        if (! $this->address) {
            return;
        }

        try {
            /** @var \FluxErp\Models\Ticket $ticket */
            $ticket = CreateTicket::make([
                'client_id' => $this->address->client_id ?? Client::default()->id,
                'authenticatable_type' => morph_alias(Address::class),
                'authenticatable_id' => $this->address->id,
                'title' => $communication->subject,
                'description' => $communication->text_body ?? $communication->html_body,
            ])
                ->validate()
                ->execute();
        } catch (ValidationException) {
            return;
        }

        $ticket->communications()->attach($communication->getKey());

        foreach ($communication->getMedia('attachments') as $attachment) {
            try {
                /** @var Media $attachment */
                $attachment->copy($ticket);
            } catch (\Throwable) {
            }
        }
    }
}
