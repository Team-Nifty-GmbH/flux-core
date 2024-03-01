<?php

namespace FluxErp\Rulesets\WorkTimeType;

use FluxErp\Models\WorkTimeType;
use FluxErp\Rulesets\FluxRuleset;

class CreateWorkTimeTypeRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTimeType::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:work_time_types,uuid',
            'name' => 'required|string',
            'is_billable' => 'boolean',
        ];
    }
}
