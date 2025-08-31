<?php

namespace FluxErp\Rulesets\EmployeeBalanceAdjustment;

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Models\Employee;
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
                'string',
                Rule::enum(EmployeeBalanceAdjustmentTypeEnum::class),
            ],
            'amount' => [
                'required',
                'numeric',
            ],
            'effective_date' => [
                'required',
                'date',
            ],
            'reason' => [
                'required',
                'string',
                Rule::enum(EmployeeBalanceAdjustmentReasonEnum::class),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
}
