<?php

namespace FluxErp\Actions\CountryRegion;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CountryRegion;
use FluxErp\Rulesets\CountryRegion\DeleteCountryRegionRuleset;

class DeleteCountryRegion extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCountryRegionRuleset::class, 'getRules');
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
