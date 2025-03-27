<?php

namespace FluxErp\Rulesets\CountryRegion;

use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateCountryRegionRuleset extends FluxRuleset
{
    protected static ?string $model = CountryRegion::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:country_regions,uuid',
            'country_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'name' => 'required|string|max:255',
        ];
    }
}
