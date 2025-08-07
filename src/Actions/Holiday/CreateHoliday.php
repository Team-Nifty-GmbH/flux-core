<?php

namespace FluxErp\Actions\Holiday;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Holiday;
use FluxErp\Rulesets\Holiday\CreateHolidayRuleset;

class CreateHoliday extends FluxAction
{
    public static function models(): array
    {
        return [Holiday::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateHolidayRuleset::class;
    }

    public function performAction(): Holiday
    {
        $holiday = app(Holiday::class, ['attributes' => $this->getData()]);
        $holiday->save();

        return $holiday->fresh();
    }
}