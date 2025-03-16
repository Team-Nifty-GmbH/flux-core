<?php

namespace FluxErp\Rulesets\WorkTimeType;

use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteWorkTimeTypeRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = WorkTimeType::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTimeType::class]),
            ],
        ];
    }
}
