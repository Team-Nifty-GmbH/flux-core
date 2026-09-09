<?php

namespace FluxErp\Actions\LoanInstallmentTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Rulesets\LoanInstallmentTransaction\DeleteLoanInstallmentTransactionRuleset;

class DeleteLoanInstallmentTransaction extends FluxAction
{
    public static function models(): array
    {
        return [LoanInstallmentTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLoanInstallmentTransactionRuleset::class;
    }

    public function performAction(): bool
    {
        return resolve_static(LoanInstallmentTransaction::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first()
            ->delete();
    }
}
