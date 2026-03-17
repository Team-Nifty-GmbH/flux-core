<?php

namespace FluxErp\Rulesets\EmployeeDay;

use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDay;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class BulkCloseEmployeeDayRuleset extends FluxRuleset
{
    protected static ?string $model = EmployeeDay::class;

    public function rules(): array
    {
        return [
            'employees' => 'required|array',
            'employees.*' => [
                'required',
                'integer',
                'distinct',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
            'timeframe' => 'required|array',
            'timeframe.0' => [
                'required',
                'date',
                'before:today',
            ],
            'timeframe.1' => [
                'nullable',
                'date',
                'after:timeframe.0',
                'before:today',
            ],
        ];
    }
}
