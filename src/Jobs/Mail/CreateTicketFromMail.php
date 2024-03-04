<?php

namespace FluxErp\Jobs\Mail;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateTicketFromMail
{
    use Batchable, Dispatchable;

    public function __construct(protected CreateMailMessage $mailMessage)
    {
    }

    public function handle(): void
    {
        $mailMessage = $this->mailMessage->getResult();

        $address = null;
        if ($mailMessage->from) {
            $addresses = app(Address::class)->query()
                ->where('email', $mailMessage->from)
                ->get();

            if ($addresses->count() > 0) {
                $address = $addresses->firstWhere('is_main_address', true) ?: $addresses->first();
            }
        }

        if (! $address) {
            $address = CreateContact::make([
                'client_id' => resolve_static(Client::class, 'default')->id,
                'main_address' => [
                    'email' => $mailMessage->from,
                ],
            ])
                ->validate()
                ->execute()
                ->mainAddress;
        }

        CreateTicket::make([
            'authenticatable_type' => app(Address::class)->getMorphClass(),
            'authenticatable_id' => $address->id,
            'title' => $mailMessage->subject,
            'description' => $mailMessage->text_body,
        ])->validate()->execute();
    }
}
