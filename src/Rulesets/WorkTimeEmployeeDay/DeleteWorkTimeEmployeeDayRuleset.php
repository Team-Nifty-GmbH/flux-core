<?php

namespace FluxErp\Rulesets\WorkTimeEmployeeDay;

use FluxErp\Models\Pivots\WorkTimeEmployeeDay;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteWorkTimeEmployeeDayRuleset extends FluxRuleset
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
        ];
    }
}
