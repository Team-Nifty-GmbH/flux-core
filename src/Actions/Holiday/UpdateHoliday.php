<?php

namespace FluxErp\Actions\Holiday;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Holiday;
use FluxErp\Rulesets\Holiday\UpdateHolidayRuleset;

class UpdateHoliday extends FluxAction
{
    public static function models(): array
    {
        return [Holiday::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateHolidayRuleset::class;
    }

    public function performAction(): Holiday
    {
        $holiday = resolve_static(Holiday::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $holiday->fill($this->getData());
        $holiday->save();

        return $holiday->fresh();
    }
}