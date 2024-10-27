<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CountryRegion;
use FluxErp\Rulesets\CountryRegion\DeleteCountryRegionRuleset;

class DeleteCountryRegion extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteCountryRegionRuleset::class;
    }

    public static function models(): array
    {
        return [CountryRegion::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(CountryRegion::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
