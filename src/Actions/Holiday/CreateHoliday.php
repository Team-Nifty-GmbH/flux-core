<?php

namespace FluxErp\Actions\Holiday;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Holiday;
use FluxErp\Rulesets\Holiday\CreateHolidayRuleset;
use Illuminate\Support\Arr;

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
        $data = $this->getData();
        $locations = Arr::pull($data, 'locations');

        $holiday = app(Holiday::class, ['attributes' => $data]);
        $holiday->save();

        if ($locations) {
            $holiday->locations()->attach($locations);
        }

        return $holiday->refresh();
    }
}
