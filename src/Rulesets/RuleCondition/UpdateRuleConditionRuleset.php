<?php

namespace FluxErp\Rulesets\RuleCondition;

use FluxErp\Models\RuleCondition;
use FluxErp\RuleEngine\ConditionRegistry;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule as ValidationRule;

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
            'type' => [
                'sometimes',
                'required',
                'string',
                ValidationRule::in(array_keys(app(ConditionRegistry::class)->all())),
            ],
            'value' => 'nullable|array',
            'position' => 'nullable|integer',
        ];
    }
}
