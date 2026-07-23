<?php

namespace FluxErp\Rulesets\Loan;

use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateLoanRuleset extends FluxRuleset
{
    protected static ?string $model = Loan::class;

    public function rules(): array
    {
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
            'amount' => [
                'sometimes',
                'required',
                app(Numeric::class, ['min' => 0.01]),
            ],
            'interest_rate' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'repayment_type_enum' => [
                'sometimes',
                'required',
                Rule::enum(RepaymentTypeEnum::class),
            ],
            'number_of_installments' => 'sometimes|required|integer|min:1',
            'starts_at' => 'sometimes|required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'installment_amount' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'note' => 'string|max:255|nullable',
        ];
    }
}
