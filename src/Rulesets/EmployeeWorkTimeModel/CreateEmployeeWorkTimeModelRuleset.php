<?php

namespace FluxErp\Rulesets\EmployeeWorkTimeModel;

use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateEmployeeWorkTimeModelRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeWorkTimeModel::class;

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
                app(ModelExists::class, ['model' => WorkTimeModel::class]),
            ],
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'annual_vacation_days' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 365]),
            ],
            'note' => 'nullable|string',
        ];
    }
}
