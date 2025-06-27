<?php

namespace FluxErp\Rulesets\Target;

use FluxErp\Models\Target;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteTargetRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Target::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Target::class])
                    ->whereNull('parent_id'),
            ],
        ];
    }
}
