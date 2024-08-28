<?php

namespace FluxErp\Rulesets\PaymentRun;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class OrderRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'orders' => 'required|array',
            'orders.*.order_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'orders.*.amount' => 'required|numeric|not_in:0',
        ];
    }
}
