<?php

namespace FluxErp\Actions\Location;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Location;
use FluxErp\Rulesets\Location\UpdateLocationRuleset;
use Illuminate\Support\Arr;

class UpdateLocation extends FluxAction
{
    public static function models(): array
    {
        return [Location::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLocationRuleset::class;
    }

    public function performAction(): Location
    {
        $location = resolve_static(Location::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $data = $this->getData();
        $holidayIds = Arr::pull($data, 'holiday_ids');

        $location->fill($data);
        $location->save();

        if (is_array($holidayIds)) {
            $location->holidays()->sync($holidayIds);
        }

        return $location->fresh();
    }
}
