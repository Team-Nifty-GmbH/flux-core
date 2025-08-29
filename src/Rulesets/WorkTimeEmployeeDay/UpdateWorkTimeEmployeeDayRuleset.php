<?php

namespace FluxErp\Rulesets\WorkTimeEmployeeDay;

use FluxErp\Models\EmployeeDay;
use FluxErp\Models\Pivots\WorkTimeEmployeeDay;
use FluxErp\Models\WorkTime;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateWorkTimeEmployeeDayRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTimeEmployeeDay::class;

    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTimeEmployeeDay::class, 'key' => 'pivot_id']),
            ],
            'work_time_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTime::class]),
            ],
            'employee_day_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => EmployeeDay::class]),
            ],
        ];
    }
}
