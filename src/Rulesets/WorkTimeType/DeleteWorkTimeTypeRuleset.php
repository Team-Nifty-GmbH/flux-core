<?php

namespace FluxErp\Rulesets\WorkTimeType;

use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteWorkTimeTypeRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTimeType::class;

    protected static bool $addAdditionalColumnRules = false;

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
