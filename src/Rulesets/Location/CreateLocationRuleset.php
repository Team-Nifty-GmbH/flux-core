<?php

namespace FluxErp\Rulesets\Location;

use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateLocationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'country_id' => [
                'required_with:country_region_id',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'country_region_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => CountryRegion::class]),
            ],
            'name' => 'required|string|max:255',
            'zip' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
