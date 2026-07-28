<?php

namespace FluxErp\Rulesets\Loan;

use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLoanRuleset extends FluxRuleset
{
    protected static ?string $model = Loan::class;

    public function rules(): array
    {
        // Schedule-affecting fields (amount, interest_rate, repayment type,
        // installment count, dates) stay locked after creation; changing them
        // is a rescheduling concern, handled by its own action later.
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Loan::class]),
            ],
            'contact_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'ledger_account_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'order_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'number' => 'string|max:255|nullable',
        ];
    }
}
