<?php

namespace FluxErp\Rulesets\OrderTransaction;

use FluxErp\Models\Order;
use FluxErp\Models\Transaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateOrderTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'transaction_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Transaction::class]),
            ],
            'order_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'amount' => [
                'required',
                app(Numeric::class),
            ],
            'is_accepted' => 'boolean',
        ];
    }
}
