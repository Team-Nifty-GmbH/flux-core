<?php

namespace FluxErp\Actions\LedgerAccountTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\LedgerAccountTransaction;
use FluxErp\Rulesets\LedgerAccountTransaction\UpdateLedgerAccountTransactionRuleset;

class UpdateLedgerAccountTransaction extends FluxAction
{
    public static function models(): array
    {
        return [LedgerAccountTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLedgerAccountTransactionRuleset::class;
    }

    public function performAction(): LedgerAccountTransaction
    {
        $ledgerAccountTransaction = resolve_static(LedgerAccountTransaction::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first();
        $ledgerAccountTransaction->fill($this->getData());
        $ledgerAccountTransaction->save();

        return $ledgerAccountTransaction->withoutRelations()->fresh();
    }
}
