<?php

namespace FluxErp\Actions\LedgerAccountTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\LedgerAccountTransaction;
use FluxErp\Rulesets\LedgerAccountTransaction\CreateLedgerAccountTransactionRuleset;

class CreateLedgerAccountTransaction extends FluxAction
{
    public static function models(): array
    {
        return [LedgerAccountTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLedgerAccountTransactionRuleset::class;
    }

    public function performAction(): LedgerAccountTransaction
    {
        /** @var LedgerAccountTransaction $ledgerAccountTransaction */
        $ledgerAccountTransaction = app(LedgerAccountTransaction::class, ['attributes' => $this->getData()]);
        $ledgerAccountTransaction->save();

        return $ledgerAccountTransaction->refresh();
    }
}
