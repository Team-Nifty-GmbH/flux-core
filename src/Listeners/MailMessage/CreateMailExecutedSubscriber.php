<?php

namespace FluxErp\Listeners\MailMessage;

use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Lead\CreateLead;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\Address;
use FluxErp\Models\Communication;
use FluxErp\Models\Currency;
use FluxErp\Models\Lead;
use FluxErp\Models\Media;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\Tenant;
use FluxErp\Models\Ticket;
use FluxErp\Settings\TicketSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Support\CauserResolver;
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
        // Mails from one of our own domains never originate from a supplier.
        $contact = $this->isTenantDomain($message->from) ? null : $this->address?->contact;

        $created = [];
        foreach ($message->getMedia('attachments') as $attachment) {
            try {
                $purchaseInvoice = CreatePurchaseInvoice::make([
                    'tenant_id' => $contact?->getTenantId() ?? resolve_static(Tenant::class, 'default')
                        ->getKey(),
                    'contact_id' => $contact?->id,
                    'currency_id' => $contact?->currency_id ?? resolve_static(Currency::class, 'default')
                        ->getKey(),
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
                'tenant_id' => $this->address->getTenantId()
                    ?? resolve_static(Tenant::class, 'default')->getKey(),
                'authenticatable_type' => morph_alias(Address::class),
                'authenticatable_id' => $this->address->getKey(),
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

        if ($templateId = app(TicketSettings::class)->auto_reply_email_template_id) {
            try {
                SendMail::make([
                    'template_id' => $templateId,
                    'to' => [Str::between($communication->from, '<', '>') ?: $communication->from],
                    'blade_parameters' => [
                        'ticket' => $ticket,
                    ],
                    'communicatables' => [
                        [
                            'model_type' => $ticket->getMorphClass(),
                            'model_id' => $ticket->getKey(),
                        ],
                    ],
                ])
                    ->validate()
                    ->execute();
            } catch (Throwable $e) {
                $activity = activity()
                    ->event('ticket_auto_reply_failed')
                    ->performedOn($ticket);

                if ($e instanceof ValidationException) {
                    $activity->withProperties([
                        'errors' => $e->errors(),
                        'template_id' => $templateId,
                    ]);
                } else {
                    $activity->withProperties([
                        'error' => $e->getMessage(),
                        'template_id' => $templateId,
                    ]);
                }

                $activity->log(class_basename($e));
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
            app(CauserResolver::class)->setCauser($this->address);
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

            $textBody = $this->stripQuotedReply($message->text_body);
            $htmlBody = $this->stripQuotedReply($message->html_body);

            try {
                $comment = CreateComment::make([
                    'model_type' => $model,
                    'model_id' => $id,
                    'comment' => nl2br($textBody ?? '') ?: $htmlBody ?? $message->subject,
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

    protected function isTenantDomain(?string $from): bool
    {
        $domain = $this->normalizeDomain(Str::between($from ?? '', '<', '>'));

        if (blank($domain)) {
            return false;
        }

        return resolve_static(Tenant::class, 'query')
            ->withoutEagerLoads()
            ->get(['email', 'website'])
            ->flatMap(fn (Tenant $tenant): array => [
                $this->normalizeDomain($tenant->email),
                $this->normalizeDomain($tenant->website),
            ])
            // `website` is only validated as a string, so drop anything without a dot
            // to keep a stray value like "test" from matching every ".test" sender.
            ->filter(fn (string $tenantDomain): bool => str_contains($tenantDomain, '.'))
            ->contains(
                fn (string $tenantDomain): bool => $domain === $tenantDomain
                    || str_ends_with($domain, '.' . $tenantDomain)
            );
    }

    protected function normalizeDomain(?string $value): string
    {
        return Str::of($value ?? '')
            ->lower()
            ->trim()
            ->afterLast('@')
            ->replaceMatches('#^[a-z][a-z\d+.-]*://#', '')
            ->before('/')
            ->before(':')
            ->replaceMatches('#^www\.#', '')
            ->trim('.')
            ->value();
    }

    protected function stripQuotedReply(?string $body): ?string
    {
        if ($body === null) {
            return null;
        }

        $position = mb_strpos($body, '[flux:quote]');

        return $position === false
            ? $body
            : rtrim(mb_substr($body, 0, $position));
    }
}
