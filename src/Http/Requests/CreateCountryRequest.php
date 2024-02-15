<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;

class CreateCountryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:countries,uuid',
            'language_id' => [
                'required',
                'integer',
                new ModelExists(Language::class),
            ],
            'currency_id' => [
                'required',
                'integer',
                new ModelExists(Currency::class),
            ],
            'name' => 'required|string',
            'iso_alpha2' => 'required|string|unique:countries,iso_alpha2',
            'iso_alpha3' => 'string',
            'iso_numeric' => 'string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_eu_country' => 'boolean',
        ];
    }
}
