<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Country;
use FluxErp\Rules\ModelExists;

class CreateCountryRegionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:country_regions,uuid',
            'country_id' => [
                'required',
                'integer',
                new ModelExists(Country::class),
            ],
            'name' => 'required|string',
        ];
    }
}
