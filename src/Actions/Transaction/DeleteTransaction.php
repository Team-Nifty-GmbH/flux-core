<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Transaction;
use FluxErp\Rulesets\Transaction\DeleteTransactionRuleset;

class DeleteTransaction extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteTransactionRuleset::class;
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Transaction::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
