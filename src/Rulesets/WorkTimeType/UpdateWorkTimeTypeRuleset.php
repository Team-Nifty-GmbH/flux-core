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
                app(ModelExists::class, ['model' => WorkTimeType::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'is_billable' => 'boolean',
        ];
    }
}
