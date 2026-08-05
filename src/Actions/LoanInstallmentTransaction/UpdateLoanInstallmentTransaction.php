<?php

namespace FluxErp\Actions\LoanInstallmentTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Rulesets\LoanInstallmentTransaction\UpdateLoanInstallmentTransactionRuleset;

class UpdateLoanInstallmentTransaction extends FluxAction
{
    public static function models(): array
    {
        return [LoanInstallmentTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLoanInstallmentTransactionRuleset::class;
    }

    public function performAction(): LoanInstallmentTransaction
    {
        $loanInstallmentTransaction = resolve_static(LoanInstallmentTransaction::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first();
        $loanInstallmentTransaction->fill($this->getData());
        $loanInstallmentTransaction->save();

        return $loanInstallmentTransaction->withoutRelations()->fresh();
    }
}
