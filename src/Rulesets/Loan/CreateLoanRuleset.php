<?php

namespace FluxErp\Rulesets\Loan;

use FluxErp\Enums\RepaymentTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Loan;
use FluxErp\Models\Order;
use FluxErp\Models\Tenant;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateLoanRuleset extends FluxRuleset
{
    protected static ?string $model = Loan::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:loans,uuid',
            'contact_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'ledger_account_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'order_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'tenant_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
            'name' => 'required|string|max:255',
            'number' => 'string|max:255|nullable',
            'amount' => [
                'required',
                app(Numeric::class, ['min' => 0.01]),
            ],
            'interest_rate' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'repayment_type_enum' => [
                'required',
                Rule::enum(RepaymentTypeEnum::class),
            ],
            'number_of_installments' => 'required|integer|min:1',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'installment_amount' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'note' => 'string|max:255|nullable',
        ];
    }
}
