<?php

namespace FluxErp\Rulesets\Location;

use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Holiday;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateLocationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'country_region_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => CountryRegion::class]),
            ],
            'latitude' => 'nullable|numeric|min:-90|max:90',
            'longitude' => 'nullable|numeric|min:-180|max:180',
            'is_active' => 'boolean',
            'holiday_ids' => 'nullable|array',
            'holiday_ids.*' => [
                'integer',
                app(ModelExists::class, ['model' => Holiday::class]),
            ],
            'client_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
        ];
    }
}
