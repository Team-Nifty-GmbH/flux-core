<?php

namespace FluxErp\Actions\VacationCarryoverRule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rulesets\VacationCarryoverRule\CreateVacationCarryoverRuleRuleset;

class CreateVacationCarryoverRule extends FluxAction
{
    public static function models(): array
    {
        return [VacationCarryoverRule::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateVacationCarryoverRuleRuleset::class;
    }

    public function performAction(): VacationCarryoverRule
    {
        $vacationCarryoverRule = app(VacationCarryoverRule::class, ['attributes' => $this->getData()]);
        $vacationCarryoverRule->save();

        return $vacationCarryoverRule->fresh();
    }
}
