<?php

namespace FluxErp\Actions\Location;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Location;
use FluxErp\Rulesets\Location\CreateLocationRuleset;

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
        $location = app(Location::class, ['attributes' => $this->getData()]);
        $location->save();

        return $location->fresh();
    }
}