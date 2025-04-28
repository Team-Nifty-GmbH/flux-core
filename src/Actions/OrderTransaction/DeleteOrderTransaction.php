<?php

namespace FluxErp\Actions\OrderTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Rulesets\OrderTransaction\DeleteOrderTransactionRuleset;

class DeleteOrderTransaction extends FluxAction
{
    public static function models(): array
    {
        return [OrderTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteOrderTransactionRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(OrderTransaction::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first()
            ->delete();
    }
}
