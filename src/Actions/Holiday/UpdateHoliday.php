<?php

namespace FluxErp\Actions\Holiday;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Holiday;
use FluxErp\Rulesets\Holiday\UpdateHolidayRuleset;
use Illuminate\Support\Arr;

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
            ->firstOrFail();

        $data = $this->getData();
        $locations = Arr::pull($data, 'locations');

        $holiday->fill($data);
        $holiday->save();

        if (is_array($locations)) {
            $holiday->locations()->sync($locations);
        }

        return $holiday->withoutRelations()->fresh();
    }
}
