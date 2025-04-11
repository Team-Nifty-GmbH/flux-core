<?php

namespace FluxErp\Rulesets\OrderTransaction;

use FluxErp\Models\Order;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\Transaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateOrderTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderTransaction::class]),
            ],
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
