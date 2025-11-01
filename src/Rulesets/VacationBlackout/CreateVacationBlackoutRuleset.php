<?php

namespace FluxErp\Rulesets\VacationBlackout;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateVacationBlackoutRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',

            'employees' => 'nullable|array',
            'employees.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],

            'employee_departments' => 'nullable|array',
            'employee_departments.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDepartment::class]),
            ],

            'locations' => 'nullable|array',
            'locations.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Location::class]),
            ],
        ];
    }
}
