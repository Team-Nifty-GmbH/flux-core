<?php

namespace FluxErp\Rulesets\EmployeeWorkTimeModel;

use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateEmployeeWorkTimeModelRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeWorkTimeModel::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:employee_work_time_models,uuid',
            'employee_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'work_time_model_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTimeModel::class]),
            ],
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ];
    }
}
