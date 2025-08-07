<?php

namespace FluxErp\Rulesets\Location;

use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Location;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class UpdateLocationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Location::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country_id' => [
                'nullable',
                'integer',
                new ModelExists(Country::class),
            ],
            'country_region_id' => [
                'nullable',
                'integer',
                new ModelExists(CountryRegion::class),
            ],
            'latitude' => 'nullable|numeric|min:-90|max:90',
            'longitude' => 'nullable|numeric|min:-180|max:180',
            'is_active' => 'boolean',
            'client_id' => [
                'sometimes',
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}