<?php

namespace FluxErp\Rulesets\EmployeeDepartment;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateEmployeeDepartmentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
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
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('employee_departments', 'code')
                    ->where('deleted_at'),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
