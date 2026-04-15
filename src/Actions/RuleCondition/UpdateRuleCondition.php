<?php

namespace FluxErp\Actions\RuleCondition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RuleCondition;
use FluxErp\Rulesets\RuleCondition\UpdateRuleConditionRuleset;

class UpdateRuleCondition extends FluxAction
{
    public static function models(): array
    {
        return [RuleCondition::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateRuleConditionRuleset::class;
    }

    public function performAction(): RuleCondition
    {
        $condition = resolve_static(RuleCondition::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $condition->fill($this->getData());
        $condition->save();

        return $condition->fresh();
    }
}
