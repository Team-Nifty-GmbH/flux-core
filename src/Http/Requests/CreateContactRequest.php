<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Category;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\DiscountGroup;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelExists;
use Illuminate\Support\Arr;

class CreateContactRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $addressRules = (new CreateAddressRequest())->rules();
        unset($addressRules['contact_id'], $addressRules['client_id']);

        return array_merge(
            (new Contact())->hasAdditionalColumnsValidationRules(),
            Arr::prependKeysWith($addressRules, 'main_address.'),
            [
                'uuid' => 'string|uuid|unique:contacts,uuid',
                'client_id' => [
                    'required',
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
                    new ModelExists(PaymentType::class),
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
                'has_sensitive_reminder' => 'sometimes|boolean',
                'has_delivery_lock' => 'sometimes|boolean',

                'main_address' => 'array',

                'discount_groups' => 'array',
                'discount_groups.*' => [
                    'required',
                    'integer',
                    new ModelExists(DiscountGroup::class),
                ],

                'categories' => 'array',
                'categories.*' => [
                    'required',
                    'integer',
                    (new ModelExists(Category::class))->where('model_type', Contact::class),
                ],
            ]
        );
    }
}
