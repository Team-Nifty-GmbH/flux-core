<?php

namespace FluxErp\Rulesets\LoanInstallmentTransaction;

use FluxErp\Models\LoanInstallment;
use FluxErp\Models\Pivots\LoanInstallmentTransaction;
use FluxErp\Models\Transaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLoanInstallmentTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LoanInstallmentTransaction::class]),
            ],
            'loan_installment_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, [
                    'model' => LoanInstallment::class,
                    'subject' => LoanInstallmentTransaction::class,
                    'subjectKeyName' => 'pivot_id',
                ]),
            ],
            'transaction_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, [
                    'model' => Transaction::class,
                    'subject' => LoanInstallmentTransaction::class,
                    'subjectKeyName' => 'pivot_id',
                ]),
            ],
            'amount' => [
                'sometimes',
                'required',
                app(Numeric::class),
            ],
            'note' => 'string|max:255|nullable',
            'is_accepted' => 'boolean',
        ];
    }
}
