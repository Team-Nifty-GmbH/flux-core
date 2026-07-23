<?php

namespace FluxErp\Actions\RuleCondition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RuleCondition;
use FluxErp\Rulesets\RuleCondition\DeleteRuleConditionRuleset;

class DeleteRuleCondition extends FluxAction
{
    public static function models(): array
    {
        return [RuleCondition::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteRuleConditionRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(RuleCondition::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
