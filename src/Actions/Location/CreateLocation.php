<?php

namespace FluxErp\Actions\Location;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
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

        return $location->refresh();
    }

    protected function prepareForValidation(): void
    {
        if ($this->getData('country_region_id') && $this->getData('country_id')) {
            $this->mergeRules([
                'country_region_id' => [
                    'nullable',
                    'integer',
                    app(ModelExists::class, ['model' => CountryRegion::class])
                        ->where('country_id', $this->getData('country_id')),
                ],
            ]);
        }
    }
}
