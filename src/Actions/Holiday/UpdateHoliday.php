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
            ->first();

        $data = $this->getData();
        $locationIds = Arr::pull($data, 'location_ids');

        $holiday->fill($data);
        $holiday->save();

        if (is_array($locationIds)) {
            $holiday->locations()->sync($locationIds);
        }

        return $holiday->fresh();
    }
}
