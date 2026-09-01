<?php

namespace FluxErp\Actions\Loan;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Loan;
use FluxErp\Rulesets\Loan\DeleteLoanRuleset;

class DeleteLoan extends FluxAction
{
    public static function models(): array
    {
        return [Loan::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLoanRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
