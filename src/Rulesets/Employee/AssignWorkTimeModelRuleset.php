<?php

namespace FluxErp\Rulesets\Employee;

use FluxErp\Models\Employee;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class AssignWorkTimeModelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'work_time_model_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTimeModel::class])
                    ->where('is_active', true),
            ],
            'valid_from' => 'required|date',
            'annual_vacation_days' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ];
    }
}
