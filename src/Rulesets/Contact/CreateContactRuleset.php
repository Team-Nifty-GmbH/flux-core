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
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateContactRuleset extends FluxRuleset
{
    protected static ?string $model = Contact::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(MainAddressRuleset::class, 'getRules'),
            resolve_static(DiscountRuleset::class, 'getRules'),
            resolve_static(DiscountGroupRuleset::class, 'getRules'),
            resolve_static(CategoryRuleset::class, 'getRules'),
            resolve_static(IndustryRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:contacts,uuid',
            'approval_user_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class])
                    ->where('is_active', true),
            ],
            'client_id' => [
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
            'customer_number' => 'string|nullable|unique:contacts,customer_number',
            'creditor_number' => 'string|nullable|unique:contacts,creditor_number',
            'debtor_number' => 'string|nullable|unique:contacts,debtor_number',
            'payment_target_days' => 'sometimes|integer|min:1|nullable',
            'payment_reminder_days_1' => 'sometimes|integer|min:1|nullable',
            'payment_reminder_days_2' => 'sometimes|integer|min:1|nullable',
            'payment_reminder_days_3' => 'sometimes|integer|min:1|nullable',
            'discount_days' => 'sometimes|integer|min:1|nullable',
            'discount_percent' => 'sometimes|numeric|min:0|max:100|nullable',
            'credit_line' => 'sometimes|numeric|min:0|nullable',
            'vat_id' => 'sometimes|string|nullable',
            'vendor_customer_number' => 'sometimes|string|nullable',
            'header' => 'string|nullable',
            'footer' => 'string|nullable',
            'has_sensitive_reminder' => 'sometimes|boolean',
            'has_delivery_lock' => 'sometimes|boolean',
        ];
    }
}
