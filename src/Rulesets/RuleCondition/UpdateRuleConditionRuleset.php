<?php

namespace FluxErp\Rulesets\RuleCondition;

use FluxErp\Models\RuleCondition;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateRuleConditionRuleset extends FluxRuleset
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
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => RuleCondition::class]),
            ],
            'type' => 'sometimes|required|string|max:255',
            'value' => 'nullable|array',
            'position' => 'nullable|integer',
        ];
    }
}
