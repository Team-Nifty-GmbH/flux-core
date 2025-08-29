<?php

namespace FluxErp\Rulesets\EmployeeBalanceAdjustment;

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeBalanceAdjustment;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateEmployeeBalanceAdjustmentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(EmployeeBalanceAdjustment::class),
            ],
            'employee_id' => [
                'sometimes',
                'required',
                'integer',
                new ModelExists(Employee::class),
            ],
            'type' => [
                'sometimes',
                'required',
                'string',
                Rule::enum(EmployeeBalanceAdjustmentTypeEnum::class),
            ],
            'amount' => [
                'sometimes',
                'required',
                'numeric',
            ],
            'effective_date' => [
                'sometimes',
                'required',
                'date',
            ],
            'reason' => [
                'sometimes',
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
