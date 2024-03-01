<?php

namespace FluxErp\Rulesets\CountryRegion;

use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCountryRegionRuleset extends FluxRuleset
{
    protected static ?string $model = CountryRegion::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CountryRegion::class),
            ],
            'country_id' => [
                'integer',
                new ModelExists(Country::class),
            ],
            'name' => 'string',
        ];
    }
}
