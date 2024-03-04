<?php

namespace FluxErp\Jobs\Mail;

use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use Illuminate\Bus\Batchable;

class CreatePurchaseInvoiceFromMail
{
    use Batchable;

    public function __construct(protected CreateMailMessage $mailMessage)
    {
    }

    public function handle(): void
    {
        /** @var \FluxErp\Models\Communication $mailMessage */
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

        $data = [
            'media' => $mailMessage->media,
        ];

        if ($address) {
            $data['client_id'] = $address->client_id;
            $data['contact_id'] = $address->contact_id;
        }

        foreach ($mailMessage->getMedia('attachments') as $media) {
            if (! in_array($media->mime_type, ['application/pdf', 'image/jpeg', 'image/png'])) {
                continue;
            }

            CreatePurchaseInvoice::make($data)->validate()->execute();
        }
    }
}
