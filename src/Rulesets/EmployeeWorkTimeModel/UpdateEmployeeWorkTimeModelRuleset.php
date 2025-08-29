<?php

namespace FluxErp\Rulesets\EmployeeWorkTimeModel;

use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateEmployeeWorkTimeModelRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeWorkTimeModel::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeWorkTimeModel::class]),
            ],
            'employee_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'work_time_model_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTimeModel::class]),
            ],
            'valid_from' => 'sometimes|required|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ];
    }
}
