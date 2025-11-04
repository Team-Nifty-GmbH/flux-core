<?php

namespace FluxErp\Actions\Location;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
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
            ->firstOrFail();

        $location->fill($this->getData());
        $location->save();

        return $location->withoutRelations()->fresh();
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
