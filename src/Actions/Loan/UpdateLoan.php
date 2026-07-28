<?php

namespace FluxErp\Actions\Loan;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Loan;
use FluxErp\Rulesets\Loan\UpdateLoanRuleset;

class UpdateLoan extends FluxAction
{
    use ValidatesLoanRelationsOnTenant;

    public static function models(): array
    {
        return [Loan::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLoanRuleset::class;
    }

    public function performAction(): Loan
    {
        $loan = resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $loan->fill($this->getData());
        $loan->save();

        return $loan->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $tenantId = resolve_static(Loan::class, 'query')
            ->whereKey($this->getData('id'))
            ->value('tenant_id');

        $this->assertLoanRelationsOnTenant($tenantId);
    }
}
