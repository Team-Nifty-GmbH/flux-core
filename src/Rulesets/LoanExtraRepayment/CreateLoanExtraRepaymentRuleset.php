<?php

namespace FluxErp\Rulesets\LoanExtraRepayment;

use FluxErp\Enums\ScheduleAdjustmentTypeEnum;
use FluxErp\Models\Loan;
use FluxErp\Models\LoanExtraRepayment;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateLoanExtraRepaymentRuleset extends FluxRuleset
{
    protected static ?string $model = LoanExtraRepayment::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:loan_extra_repayments,uuid',
            'loan_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Loan::class]),
            ],
            'executed_at' => 'required|date',
            'amount' => [
                'required',
                app(Numeric::class, ['min' => 0.01]),
            ],
            'schedule_adjustment_type_enum' => [
                'required',
                app(EnumRule::class, ['type' => ScheduleAdjustmentTypeEnum::class]),
            ],
            'note' => 'string|max:255|nullable',
        ];
    }
}
