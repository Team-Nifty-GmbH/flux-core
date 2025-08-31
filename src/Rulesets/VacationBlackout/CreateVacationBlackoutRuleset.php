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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => [
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'employee_department_ids' => 'nullable|array',
            'employee_department_ids.*' => [
                'integer',
                app(ModelExists::class, ['model' => EmployeeDepartment::class]),
            ],
            'location_ids' => 'nullable|array',
            'location_ids.*' => [
                'integer',
                app(ModelExists::class, ['model' => Location::class]),
            ],
        ];
    }
}
