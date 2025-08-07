<?php

namespace FluxErp\Actions\VacationCarryoverRule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rulesets\VacationCarryoverRule\DeleteVacationCarryoverRuleRuleset;

class DeleteVacationCarryoverRule extends FluxAction
{
    public static function models(): array
    {
        return [VacationCarryoverRule::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteVacationCarryoverRuleRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(VacationCarryoverRule::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}