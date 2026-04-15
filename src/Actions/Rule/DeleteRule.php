<?php

namespace FluxErp\Actions\Rule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Rule;
use FluxErp\Rulesets\Rule\DeleteRuleRuleset;

class DeleteRule extends FluxAction
{
    public static function models(): array
    {
        return [Rule::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteRuleRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Rule::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
