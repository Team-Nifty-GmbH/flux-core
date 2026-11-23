<?php

namespace FluxErp\Rulesets\LoanInstallmentTransaction;

use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Transaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateLoanInstallmentTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'loan_installment_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LoanInstallment::class]),
            ],
            'transaction_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Transaction::class]),
            ],
            'amount' => [
                'required',
                app(Numeric::class),
            ],
            'note' => 'string|max:255|nullable',
            'is_accepted' => 'boolean',
        ];
    }
}
