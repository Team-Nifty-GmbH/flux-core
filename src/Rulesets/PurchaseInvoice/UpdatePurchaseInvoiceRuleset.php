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

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            resolve_static(PurchaseInvoicePositionRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules'),
            [
                'purchase_invoice_positions.*.id' => [
                    'integer',
                    app(ModelExists::class, ['model' => PurchaseInvoicePosition::class]),
                ],
            ]
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PurchaseInvoice::class]),
            ],
            'approval_user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class])
                    ->where('is_active', true),
            ],
            'client_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'lay_out_user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class])->where('is_active', true),
            ],
            'currency_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'order_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class]),
            ],
            'payment_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => PaymentType::class]),
            ],
            'invoice_date' => 'date',
            'system_delivery_date' => 'date|nullable|required_with:system_delivery_date_end',
            'system_delivery_date_end' => 'date|nullable|after_or_equal:system_delivery_date',
            'invoice_number' => 'nullable|string|max:255',
            'is_net' => 'boolean',

            'purchase_invoice_positions' => 'array',
        ];
    }
}
