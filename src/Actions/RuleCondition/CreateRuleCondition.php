<?php

namespace FluxErp\Actions\RuleCondition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RuleCondition;
use FluxErp\Rulesets\RuleCondition\CreateRuleConditionRuleset;

class CreateRuleCondition extends FluxAction
{
    public static function models(): array
    {
        return [RuleCondition::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateRuleConditionRuleset::class;
    }

    public function performAction(): RuleCondition
    {
        $condition = app(RuleCondition::class, ['attributes' => $this->getData()]);
        $condition->save();

        return $condition->fresh();
    }
}
