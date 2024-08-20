<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\WorkTime;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteWorkTimeRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTime::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTime::class]),
            ],
        ];
    }
}
