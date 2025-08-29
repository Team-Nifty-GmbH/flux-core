<?php

namespace FluxErp\Rulesets\EmployeeDay;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateEmployeeDayRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeDay::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDay::class]),
            ],
            'employee_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'date' => 'sometimes|required|date',
            'target_hours' => 'sometimes|required|numeric|min:0|max:24',
            'actual_hours' => 'sometimes|required|numeric|min:0|max:24',
            'break_minutes' => 'nullable|numeric|min:0|max:1440',
            'plus_minus_vacation_hours' => 'nullable|numeric|min:-365|max:365',
            'plus_minus_overtime_hours' => 'nullable|numeric|min:-1000|max:1000',
            'absence_requests' => 'nullable|array',
            'work_times' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'is_work_day' => 'required|boolean',
        ];
    }
}
