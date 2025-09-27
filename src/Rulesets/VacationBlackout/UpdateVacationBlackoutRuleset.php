<?php

namespace FluxErp\Rulesets\VacationBlackout;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Location;
use FluxErp\Models\VacationBlackout;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateVacationBlackoutRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => VacationBlackout::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required_with:end_date|date',
            'end_date' => 'required_with:start_date|date|after_or_equal:start_date',
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
