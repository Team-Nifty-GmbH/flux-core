<?php

namespace FluxErp\Actions\VacationCarryoverRule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationCarryOverRule;
use FluxErp\Rulesets\VacationCarryoverRule\UpdateVacationCarryoverRuleRuleset;

class UpdateVacationCarryoverRule extends FluxAction
{
    public static function models(): array
    {
        return [VacationCarryOverRule::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateVacationCarryoverRuleRuleset::class;
    }

    public function performAction(): VacationCarryOverRule
    {
        $vacationCarryoverRule = resolve_static(VacationCarryOverRule::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $vacationCarryoverRule->fill($this->getData());
        $vacationCarryoverRule->save();

        return $vacationCarryoverRule->fresh();
    }
}
