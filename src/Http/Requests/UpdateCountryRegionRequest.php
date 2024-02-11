<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Rules\ModelExists;

class UpdateCountryRegionRequest extends BaseFormRequest
{
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
