<?php

namespace FluxErp\Rulesets\PurchaseInvoice;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rulesets\PurchaseInvoicePosition\UpdatePurchaseInvoicePositionRuleset;
use Illuminate\Support\Arr;

class CreateOrderFromPurchaseInvoiceRuleset extends FluxRuleset
{
    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            [
                'purchase_invoice_positions' => 'array',
            ],
            Arr::prependKeysWith(
                resolve_static(UpdatePurchaseInvoicePositionRuleset::class, 'getRules'),
                'purchase_invoice_positions.*'
            ),
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
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'contact_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'currency_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'lay_out_user_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class])
                    ->where('is_active', true),
            ],
            'order_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class]),
            ],
            'payment_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PaymentType::class]),
            ],
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'is_net' => 'boolean',
        ];
    }
}
