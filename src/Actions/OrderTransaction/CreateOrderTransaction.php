<?php

namespace FluxErp\Actions\OrderTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Rulesets\OrderTransaction\CreateOrderTransactionRuleset;
use Illuminate\Validation\ValidationException;

class CreateOrderTransaction extends FluxAction
{
    public static function models(): array
    {
        return [OrderTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateOrderTransactionRuleset::class;
    }

    public function performAction(): OrderTransaction
    {
        /** @var OrderTransaction $orderTransaction */
        $orderTransaction = app(OrderTransaction::class, ['attributes' => $this->getData()]);
        $orderTransaction->save();

        return $orderTransaction->refresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $orderTypeEnum = resolve_static(Order::class, 'query')
            ->whereKey($this->getData('order_id'))
            ->first(['id', 'order_type_id'])
            ?->orderType
            ?->order_type_enum;

        if ($orderTypeEnum?->isSubscription()) {
            throw ValidationException::withMessages([
                'order_id' => ['Transactions cannot be assigned to subscription orders.'],
            ])
                ->errorBag('createOrderTransaction');
        }
    }
}
