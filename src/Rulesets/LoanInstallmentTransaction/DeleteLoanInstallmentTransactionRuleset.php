<?php

namespace FluxErp\Rulesets\LoanInstallmentTransaction;

use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLoanInstallmentTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LoanInstallmentTransaction::class]),
            ],
        ];
    }
}
