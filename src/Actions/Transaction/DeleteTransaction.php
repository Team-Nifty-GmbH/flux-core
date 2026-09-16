<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Contracts\SupportsBulkExecution;
use FluxErp\Models\Transaction;
use FluxErp\Rulesets\Transaction\DeleteTransactionRuleset;

class DeleteTransaction extends DispatchableFluxAction implements SupportsBulkExecution
{
    public static function models(): array
    {
        return [Transaction::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteTransactionRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Transaction::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
