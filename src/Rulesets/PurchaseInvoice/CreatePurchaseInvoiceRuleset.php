<?php

namespace FluxErp\Rulesets\PurchaseInvoice;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Media;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\User;
use FluxErp\Rules\MediaUploadType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;

class CreatePurchaseInvoiceRuleset extends FluxRuleset
{
    protected static ?string $model = PurchaseInvoice::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:purchase_invoices,uuid',
            'client_id' => [
                'nullable',
                'integer',
                new ModelExists(Client::class),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                new ModelExists(Contact::class),
            ],
            'currency_id' => [
                'nullable',
                'integer',
                new ModelExists(Currency::class),
            ],
            'lay_out_user_id' => [
                'nullable',
                'integer',
                (new ModelExists(User::class))->where('is_active', true),
            ],
            'order_type_id' => [
                'nullable',
                'integer',
                new ModelExists(OrderType::class),
            ],
            'payment_type_id' => [
                'nullable',
                'integer',
                new ModelExists(PaymentType::class),
            ],
            'invoice_date' => 'nullable|date',
            'system_delivery_date' => 'date|nullable|required_with:system_delivery_date_end',
            'system_delivery_date_end' => 'date|nullable|after_or_equal:system_delivery_date',
            'invoice_number' => 'nullable|string',
            'is_net' => 'boolean',

            'media' => 'required',
            'media.id' => [
                'integer',
                new ModelExists(Media::class),
            ],
            'media_type' => ['sometimes', new MediaUploadType()],
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            resolve_static(PurchaseInvoicePositionRuleset::class, 'getRules')
        );
    }
}
