<?php

namespace FluxErp\Rulesets\EmployeeDepartment;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateEmployeeDepartmentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDepartment::class]),
            ],
            'location_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Location::class]),
            ],
            'manager_employee_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDepartment::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
