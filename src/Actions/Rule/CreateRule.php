<?php

namespace FluxErp\Actions\Rule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Rule;
use FluxErp\Rulesets\Rule\CreateRuleRuleset;

class CreateRule extends FluxAction
{
    public static function models(): array
    {
        return [Rule::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateRuleRuleset::class;
    }

    public function performAction(): Rule
    {
        $rule = app(Rule::class, ['attributes' => $this->getData()]);
        $rule->save();

        return $rule->fresh();
    }
}
