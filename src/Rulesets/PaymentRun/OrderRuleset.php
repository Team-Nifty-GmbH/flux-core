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
            'positions' => 'required_without:orders|array|min:1',
            'positions.*.contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'positions.*.iban' => ['nullable', app(Iban::class)],
            'positions.*.bic' => 'nullable|string|max:255',
            'positions.*.account_holder' => 'nullable|string|max:255',
            'positions.*.purpose' => 'nullable|string|max:140',
            'positions.*.orders' => 'required|array|min:1',
            'positions.*.orders.*.order_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'positions.*.orders.*.amount' => 'required|numeric|not_in:0',
            'orders' => 'required_without:positions|array|min:1',
            'orders.*.order_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'orders.*.amount' => 'required|numeric|not_in:0',
        ];
    }
}
