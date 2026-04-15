<?php

namespace FluxErp\Rulesets\RuleCondition;

use FluxErp\Models\Rule;
use FluxErp\Models\RuleCondition;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateRuleConditionRuleset extends FluxRuleset
{
    protected static ?string $model = RuleCondition::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:rule_conditions,uuid',
            'rule_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Rule::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => RuleCondition::class]),
            ],
            'type' => 'required|string|max:255',
            'value' => 'nullable|array',
            'position' => 'nullable|integer',
        ];
    }
}
