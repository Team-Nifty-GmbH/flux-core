<?php

namespace FluxErp\Actions\Rule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Rule;
use FluxErp\Rulesets\Rule\UpdateRuleRuleset;

class UpdateRule extends FluxAction
{
    public static function models(): array
    {
        return [Rule::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateRuleRuleset::class;
    }

    public function performAction(): Rule
    {
        $rule = resolve_static(Rule::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $rule->fill($this->getData());
        $rule->save();

        return $rule->fresh();
    }
}
