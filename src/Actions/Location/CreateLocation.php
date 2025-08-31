<?php

namespace FluxErp\Actions\Location;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Location;
use FluxErp\Rulesets\Location\CreateLocationRuleset;
use Illuminate\Support\Arr;

class CreateLocation extends FluxAction
{
    public static function models(): array
    {
        return [Location::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLocationRuleset::class;
    }

    public function performAction(): Location
    {
        $data = $this->getData();
        $holidayIds = Arr::pull($data, 'holiday_ids');

        $location = app(Location::class, ['attributes' => $data]);
        $location->save();

        if (is_array($holidayIds)) {
            $location->holidays()->sync($holidayIds);
        }

        return $location->fresh();
    }
}
