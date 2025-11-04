<?php

namespace FluxErp\Rulesets\EmployeeBalanceAdjustment;

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Models\Employee;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateEmployeeBalanceAdjustmentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                new ModelExists(Employee::class),
            ],
            'type' => [
                'required',
                Rule::enum(EmployeeBalanceAdjustmentTypeEnum::class),
            ],
            'amount' => [
                'required',
                'numeric',
                'not_in:0',
            ],
            'effective_date' => [
                'required',
                'date',
            ],
            'reason' => [
                'required',
                app(EnumRule::class, ['type' => EmployeeBalanceAdjustmentReasonEnum::class]),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
}
