<?php

namespace FluxErp\Rulesets\EmployeeDay;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CloseEmployeeDayRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeDay::class;

    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
