<?php

namespace FluxErp\Actions\VacationBlackout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationBlackout;
use FluxErp\Rulesets\VacationBlackout\DeleteVacationBlackoutRuleset;

class DeleteVacationBlackout extends FluxAction
{
    public static function models(): array
    {
        return [VacationBlackout::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteVacationBlackoutRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(VacationBlackout::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
