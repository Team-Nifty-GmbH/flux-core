<?php

namespace FluxErp\Rulesets\EmployeeDepartment;

use FluxErp\Models\EmployeeDepartment;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteEmployeeDepartmentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDepartment::class]),
            ],
        ];
    }
}
