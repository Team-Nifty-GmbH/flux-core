<?php

namespace FluxErp\Rulesets\Loan;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class FinanceOrderRuleset extends FluxRuleset
{
    protected static ?string $model = Loan::class;

    public function rules(): array
    {
        return [
            'loan_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Loan::class]),
            ],
            'order_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'debit_ledger_account_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'amount' => [
                'required',
                app(Numeric::class, ['min' => 0.01]),
            ],
            'booking_date' => 'required|date',
            'booking_text' => 'string|max:255|nullable',
            'note' => 'string|max:255|nullable',
        ];
    }
}
