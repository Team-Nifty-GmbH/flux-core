<?php

namespace FluxErp\Rulesets\RuleCondition;

use FluxErp\Models\RuleCondition;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteRuleConditionRuleset extends FluxRuleset
{
    protected static ?string $model = RuleCondition::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => RuleCondition::class]),
            ],
        ];
    }
}
