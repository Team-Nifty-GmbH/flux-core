<?php

namespace FluxErp\Listeners\MailMessage;

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Lead\CreateLead;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Communication;
use FluxErp\Models\Currency;
use FluxErp\Models\Lead;
use FluxErp\Models\Media;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\CauserResolver;
use Throwable;

class CreateMailExecutedSubscriber
{
    protected ?Address $address = null;

    public function createLead(Communication $communication): ?Lead
    {
        if (! $this->address) {
            return null;
        }

        try {
            /** @var Lead $lead */
            $lead = CreateLead::make([
                'address_id' => $this->address->getKey(),
                'name' => $communication->subject,
                'description' => $communication->text_body ?? $communication->html_body,
            ])
                ->validate()
                ->execute();
        } catch (ValidationException) {
            return null;
        }

        $lead->communications()->attach($communication->getKey());

        foreach ($communication->getMedia('attachments') as $attachment) {
            try {
                /** @var Media $attachment */
                $attachment->copy($lead);
            } catch (Throwable) {
            }
        }

        return $lead;
    }

    public function createPurchaseInvoice(Communication $message): ?Collection
    {
        $contact = $this->address?->contact;

        $created = [];
        foreach ($message->getMedia('attachments') as $attachment) {
            try {
                $purchaseInvoice = CreatePurchaseInvoice::make([
                    'client_id' => $contact?->client_id ?? resolve_static(Client::class, 'default')->getKey(),
                    'contact_id' => $contact?->id,
                    'currency_id' => $contact?->currency_id ?? resolve_static(Currency::class, 'default')->getKey(),
                    'payment_type_id' => $contact?->purchase_payment_type_id ?? $contact?->payment_type_id,
                    'invoice_date' => $message->date->toDateString(),
                    'media' => [
                        'id' => $attachment->id,
                    ],
                ])
                    ->when(
                        $this->address,
                        fn (CreatePurchaseInvoice $action) => $action->actingAs($this->address)
                    )
                    ->validate()
                    ->execute();
            } catch (Throwable) {
                continue;
            }

            $created[] = $purchaseInvoice->getKey();
            $purchaseInvoice->communications()->attach($message->getKey());
        }

        return $created
            ? resolve_static(PurchaseInvoice::class, 'query')
                ->whereKey($created)
                ->get()
            : null;
    }

    public function createTicket(Communication $communication): ?Ticket
    {
        if (! $this->address) {
            return null;
        }

        try {
            /** @var Ticket $ticket */
            $ticket = CreateTicket::make([
                'client_id' => $this->address->client_id
                    ?? resolve_static(Client::class, 'default')->getKey(),
                'authenticatable_type' => morph_alias(Address::class),
                'authenticatable_id' => $this->address->id,
                'title' => $communication->subject,
                'description' => $communication->text_body ?? $communication->html_body,
            ])
                ->actingAs($this->address)
                ->validate()
                ->execute();
        } catch (ValidationException) {
            return null;
        }

        $ticket->communications()->attach($communication->getKey());

        foreach ($communication->getMedia('attachments') as $attachment) {
            try {
                /** @var Media $attachment */
                $attachment->copy($ticket);
            } catch (Throwable) {
            }
        }

        return $ticket;
    }

    public function findAddress(Communication $communication): static
    {
        $this->address = resolve_static(
            Address::class,
            'findAddressByEmail',
            ['email' => Str::between($communication->from, '<', '>')]
        );

        if ($this->address) {
            CauserResolver::setCauser($this->address);
        }

        return $this;
    }

    public function handle(CreateMailMessage $event): void
    {
        $message = $event->getResult();
        $this->findAddress($message);

        if ($message->mailFolder->can_create_purchase_invoice && $message->media()->count() !== 0) {
            $this->createPurchaseInvoice($message);
        }

        if ($message->mailFolder->can_create_lead) {
            $this->createLead($message);
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
                $comment = CreateComment::make([
                    'model_type' => $model,
                    'model_id' => $id,
                    'comment' => nl2br($message->text_body ?? '') ?: $message->html_body ?? $message->subject,
                    'is_internal' => false,
                ])
                    ->actingAs($this->address)
                    ->validate()
                    ->execute();

                $comment->model->communications()->attach($message->getKey());
            } catch (Throwable) {
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
}
