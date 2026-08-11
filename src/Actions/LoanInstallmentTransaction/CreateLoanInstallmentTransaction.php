<?php

namespace FluxErp\Actions\LoanInstallmentTransaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Rulesets\LoanInstallmentTransaction\CreateLoanInstallmentTransactionRuleset;

class CreateLoanInstallmentTransaction extends FluxAction
{
    public static function models(): array
    {
        return [LoanInstallmentTransaction::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLoanInstallmentTransactionRuleset::class;
    }

    public function performAction(): LoanInstallmentTransaction
    {
        $loanInstallmentTransaction = app(LoanInstallmentTransaction::class, ['attributes' => $this->getData()]);
        $loanInstallmentTransaction->save();

        return $loanInstallmentTransaction->refresh();
    }
}
