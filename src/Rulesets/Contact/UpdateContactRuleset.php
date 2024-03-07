<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateContactRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Contact::class),
            ],
            'client_id' => [
                'integer',
                new ModelExists(Client::class),
            ],
            'agent_id' => [
                'integer',
                'nullable',
                new ModelExists(User::class),
            ],
            'payment_type_id' => [
                'integer',
                'nullable',
                (new ModelExists(PaymentType::class))
                    ->where('is_active', true)
                    ->where('is_sales', true),
            ],
            'purchase_payment_type_id' => [
                'integer',
                'nullable',
                (new ModelExists(PaymentType::class))
                    ->where('is_active', true)
                    ->where('is_purchase', true),
            ],
            'price_list_id' => [
                'integer',
                'nullable',
                new ModelExists(PriceList::class),
            ],
            'expense_ledger_account_id' => [
                'integer',
                'nullable',
                new ModelExists(LedgerAccount::class),
            ],
            'vat_rate_id' => [
                'integer',
                'nullable',
                new ModelExists(VatRate::class),
            ],
            'customer_number' => 'sometimes|string',
            'creditor_number' => 'string|nullable',
            'debtor_number' => 'string|nullable',
            'payment_target_days' => 'sometimes|integer|nullable',
            'payment_reminder_days_1' => 'sometimes|integer|nullable',
            'payment_reminder_days_2' => 'sometimes|integer|nullable',
            'payment_reminder_days_3' => 'sometimes|integer|nullable',
            'discount_days' => 'sometimes|integer|nullable',
            'discount_percent' => 'sometimes|numeric|nullable',
            'credit_line' => 'sometimes|numeric|nullable',
            'vat_id' => 'sometimes|string|nullable',
            'vendor_customer_number' => 'sometimes|string|nullable',
            'has_sensitive_reminder' => 'sometimes|boolean',
            'has_delivery_lock' => 'sometimes|boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(DiscountGroupRuleset::class, 'getRules'),
            resolve_static(CategoryRuleset::class, 'getRules')
        );
    }
}
