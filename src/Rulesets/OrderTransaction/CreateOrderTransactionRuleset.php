<?php

namespace FluxErp\Rulesets\OrderTransaction;

use Closure;
use FluxErp\Enums\OrderTypeEnum;
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
                function (string $attribute, mixed $value, Closure $fail): void {
                    $orderTypeEnum = resolve_static(Order::class, 'query')
                        ->whereKey($value)
                        ->first(['id', 'order_type_id'])
                        ?->orderType
                        ?->order_type_enum;

                    if ($orderTypeEnum instanceof OrderTypeEnum && $orderTypeEnum->isSubscription()) {
                        $fail(__('Transactions cannot be assigned to subscription orders.'));
                    }
                },
            ],
            'amount' => [
                'required',
                app(Numeric::class),
            ],
            'exchange_rate' => [
                'nullable',
                app(Numeric::class),
            ],
            'order_currency_amount' => [
                'nullable',
                app(Numeric::class),
            ],
            'is_accepted' => 'boolean',
        ];
    }
}
