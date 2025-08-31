<?php

namespace FluxErp\Rulesets\EmployeeDepartment;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateEmployeeDepartmentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDepartment::class]),
            ],
            'manager_employee_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'location_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Location::class]),
            ],
            'is_active' => 'boolean',
        ];
    }
}
