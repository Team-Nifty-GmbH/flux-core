<?php

namespace FluxErp\Actions\Holiday;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Holiday;
use FluxErp\Rulesets\Holiday\DeleteHolidayRuleset;

class DeleteHoliday extends FluxAction
{
    public static function models(): array
    {
        return [Holiday::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteHolidayRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Holiday::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}