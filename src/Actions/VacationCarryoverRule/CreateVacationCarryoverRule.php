<?php

namespace FluxErp\Actions\VacationCarryoverRule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationCarryOverRule;
use FluxErp\Rulesets\VacationCarryoverRule\CreateVacationCarryoverRuleRuleset;

class CreateVacationCarryoverRule extends FluxAction
{
    public static function models(): array
    {
        return [VacationCarryOverRule::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateVacationCarryoverRuleRuleset::class;
    }

    public function performAction(): VacationCarryOverRule
    {
        $vacationCarryoverRule = app(VacationCarryOverRule::class, ['attributes' => $this->getData()]);
        $vacationCarryoverRule->save();

        return $vacationCarryoverRule->fresh();
    }
}
