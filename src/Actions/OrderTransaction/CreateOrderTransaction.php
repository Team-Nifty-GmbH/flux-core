<?php

namespace FluxErp\Actions\OrderTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Rulesets\OrderTransaction\CreateOrderTransactionRuleset;

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
        $transactionAssignmentSuggestion = app(OrderTransaction::class, ['attributes' => $this->getData()]);
        $transactionAssignmentSuggestion->save();

        return $transactionAssignmentSuggestion->fresh();
    }
}
