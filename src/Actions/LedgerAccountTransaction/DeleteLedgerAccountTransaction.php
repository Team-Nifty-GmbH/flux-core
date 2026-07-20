<?php

namespace FluxErp\Actions\LedgerAccountTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\LedgerAccountTransaction;
use FluxErp\Rulesets\LedgerAccountTransaction\DeleteLedgerAccountTransactionRuleset;

class DeleteLedgerAccountTransaction extends FluxAction
{
    public static function models(): array
    {
        return [LedgerAccountTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLedgerAccountTransactionRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(LedgerAccountTransaction::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first()
            ->delete();
    }
}
