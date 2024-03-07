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
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PurchaseInvoice::class),
            ],
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'contact_id' => [
                'required',
                'integer',
                new ModelExists(Contact::class),
            ],
            'currency_id' => [
                'required',
                'integer',
                new ModelExists(Currency::class),
            ],
            'lay_out_user_id' => [
                'nullable',
                'integer',
                (new ModelExists(User::class))->where('is_active', true),
            ],
            'order_type_id' => [
                'required',
                'integer',
                new ModelExists(OrderType::class),
            ],
            'payment_type_id' => [
                'required',
                'integer',
                new ModelExists(PaymentType::class),
            ],
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
            'is_net' => 'boolean',
        ];
    }

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
}
