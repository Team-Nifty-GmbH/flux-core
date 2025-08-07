<?php

namespace FluxErp\Actions\Location;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Location;
use FluxErp\Rulesets\Location\UpdateLocationRuleset;

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

        $location->fill($this->getData());
        $location->save();

        return $location->fresh();
    }
}