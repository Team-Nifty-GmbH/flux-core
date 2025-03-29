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
                app(ModelExists::class, ['model' => CountryRegion::class]),
            ],
            'country_id' => [
                'integer',
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'name' => 'string|max:255',
        ];
    }
}
