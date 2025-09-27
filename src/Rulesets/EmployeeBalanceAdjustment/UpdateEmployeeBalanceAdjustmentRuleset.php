<?php

namespace FluxErp\Rulesets\EmployeeBalanceAdjustment;

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
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
                'not_in:0',
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
