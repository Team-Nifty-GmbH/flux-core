<?php

namespace FluxErp\Rulesets\PurchaseInvoice;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePurchaseInvoiceRuleset extends FluxRuleset
{
    protected static ?string $model = PurchaseInvoice::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PurchaseInvoice::class),
            ],
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
            'lay_out_user_id' => [
                'nullable',
                'integer',
                (new ModelExists(User::class))->where('is_active', true),
            ],
            'currency_id' => [
                'nullable',
                'integer',
                new ModelExists(Currency::class),
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
            'invoice_date' => 'date',
            'system_delivery_date' => 'date|nullable|required_with:system_delivery_date_end',
            'system_delivery_date_end' => 'date|nullable|after_or_equal:system_delivery_date',
            'invoice_number' => 'nullable|string',
            'is_net' => 'boolean',

            'purchase_invoice_positions' => 'array',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            resolve_static(PurchaseInvoicePositionRuleset::class, 'getRules'),
            [
                'purchase_invoice_positions.*.id' => [
                    'integer',
                    new ModelExists(PurchaseInvoicePosition::class),
                ],
            ]
        );
    }
}
