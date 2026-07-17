<?php

namespace FluxErp\Rulesets\RuleCondition;

use FluxErp\Models\Rule;
use FluxErp\Models\RuleCondition;
use FluxErp\RuleEngine\ConditionRegistry;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule as ValidationRule;

class CreateRuleConditionRuleset extends FluxRuleset
{
    protected static ?string $model = RuleCondition::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:rule_conditions,uuid',
            'parent_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => RuleCondition::class]),
            ],
            'rule_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Rule::class]),
            ],
            'type' => [
                'required',
                'string',
                ValidationRule::in(array_keys(app(ConditionRegistry::class)->all())),
            ],
            'value' => 'nullable|array',
            'position' => 'nullable|integer',
        ];
    }
}
