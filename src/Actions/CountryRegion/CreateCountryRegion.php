<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CountryRegion;
use FluxErp\Rulesets\CountryRegion\CreateCountryRegionRuleset;

class CreateCountryRegion extends FluxAction
{
    public static function models(): array
    {
        return [CountryRegion::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCountryRegionRuleset::class;
    }

    public function performAction(): CountryRegion
    {
        $countryRegion = app(CountryRegion::class, ['attributes' => $this->data]);
        $countryRegion->save();

        return $countryRegion->fresh();
    }
}
