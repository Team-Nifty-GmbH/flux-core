<?php

namespace FluxErp\Rulesets\PaymentRun;

use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class OrderRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'positions' => 'required|array',
            'positions.*.contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'positions.*.iban' => ['nullable', app(Iban::class)],
            'positions.*.bic' => 'nullable|string|max:255',
            'positions.*.account_holder' => 'nullable|string|max:255',
            'positions.*.orders' => 'required|array',
            'positions.*.orders.*.order_id' => [
                'required',
                'integer',
                'distinct',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'positions.*.orders.*.amount' => 'required|numeric|not_in:0',
        ];
    }
}
