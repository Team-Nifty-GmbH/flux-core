<?php

namespace FluxErp\Rulesets\WorkTimeType;

use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateWorkTimeTypeRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTimeType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(WorkTimeType::class),
            ],
            'name' => 'sometimes|required|string',
            'is_billable' => 'boolean',
        ];
    }
}
