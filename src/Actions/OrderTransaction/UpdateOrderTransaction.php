<?php

namespace FluxErp\Actions\OrderTransaction;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Rulesets\OrderTransaction\UpdateOrderTransactionRuleset;

class UpdateOrderTransaction extends DispatchableFluxAction
{
    public static function models(): array
    {
        return [OrderTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateOrderTransactionRuleset::class;
    }

    public function performAction(): OrderTransaction
    {
        $orderTransaction = resolve_static(OrderTransaction::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first();
        $orderTransaction->fill($this->getData());
        $orderTransaction->save();

        return $orderTransaction->withoutRelations()->fresh();
    }
}
