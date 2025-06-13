<?php

namespace FluxErp\Rulesets\Contact;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactOrigin;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelDoesntExist;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class RestoreContactRuleset extends FluxRuleset
{
    protected static ?string $model = Contact::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Contact::class])
                    ->onlyTrashed(),
            ],
            'approval_user_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class])
                    ->where('is_active', true),
            ],
            'client_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'agent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class])
                    ->where('is_active', true),
            ],
            'contact_origin_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => ContactOrigin::class])
                    ->where('is_active', true),
            ],
            'currency_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'payment_type_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => PaymentType::class])
                    ->where('is_active', true)
                    ->where('is_sales', true),
            ],
            'purchase_payment_type_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => PaymentType::class])
                    ->where('is_active', true)
                    ->where('is_purchase', true),
            ],
            'price_list_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'expense_ledger_account_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'vat_rate_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => VatRate::class])
                    ->where('is_tax_exemption', true),
            ],
            'customer_number' => [
                'string',
                'max:255',
                'nullable',
                app(ModelDoesntExist::class, ['model' => Contact::class, 'key' => 'customer_number']),
            ],
            'creditor_number' => [
                'string',
                'max:255',
                'nullable',
                app(ModelDoesntExist::class, ['model' => Contact::class, 'key' => 'creditor_number']),
            ],
            'debtor_number' => [
                'string',
                'max:255',
                'nullable',
                app(ModelDoesntExist::class, ['model' => Contact::class, 'key' => 'debtor_number']),
            ],
            'payment_target_days' => 'integer|min:1|nullable',
            'payment_reminder_days_1' => 'integer|min:1|nullable',
            'payment_reminder_days_2' => 'integer|min:1|nullable',
            'payment_reminder_days_3' => 'integer|min:1|nullable',
            'discount_days' => 'integer|min:1|nullable',
            'discount_percent' => 'numeric|min:0|max:100|nullable',
            'credit_line' => 'numeric|min:0|nullable',
            'vat_id' => 'string|max:255|nullable',
            'vendor_customer_number' => 'string|max:255|nullable',
            'header' => 'string|nullable',
            'footer' => 'string|nullable',
            'has_sensitive_reminder' => 'boolean',
            'has_delivery_lock' => 'boolean',
        ];
    }
}
