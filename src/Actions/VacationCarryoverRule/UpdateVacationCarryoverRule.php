<?php

namespace FluxErp\Actions\VacationCarryoverRule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rulesets\VacationCarryoverRule\UpdateVacationCarryoverRuleRuleset;

class UpdateVacationCarryoverRule extends FluxAction
{
    public static function models(): array
    {
        return [VacationCarryoverRule::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateVacationCarryoverRuleRuleset::class;
    }

    public function performAction(): VacationCarryoverRule
    {
        $vacationCarryoverRule = resolve_static(VacationCarryoverRule::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $vacationCarryoverRule->fill($this->getData());
        $vacationCarryoverRule->save();

        return $vacationCarryoverRule->fresh();
    }
}
