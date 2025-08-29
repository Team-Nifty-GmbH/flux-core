<?php

namespace FluxErp\Rulesets\EmployeeDay;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateEmployeeDayRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeDay::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:employee_days,uuid',
            'employee_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'date' => 'required|date|unique:employee_days,date,NULL,id,employee_id,' . request('employee_id'),
            'target_hours' => 'required|numeric|min:0|max:24',
            'actual_hours' => 'required|numeric|min:0|max:24',
            'break_minutes' => 'nullable|numeric|min:0|max:1440', // Max 24 hours in minutes
            'plus_minus_vacation_hours' => 'nullable|numeric|min:-365|max:365',
            'plus_minus_overtime_hours' => 'nullable|numeric|min:-1000|max:1000',
            'absence_requests' => 'nullable|array',
            'work_times' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'is_work_day' => 'required|boolean',
        ];
    }
}
