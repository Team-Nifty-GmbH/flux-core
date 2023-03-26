<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateCountryRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:countries,id,deleted_at,NULL',
            'language_id' => [
                'integer',
                (new ExistsWithIgnore('languages', 'id'))->whereNull('deleted_at'),
            ],
            'currency_id' => [
                'integer',
                (new ExistsWithIgnore('currencies', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'string',
            'iso_alpha2' => 'sometimes|required|string|unique:countries,iso_alpha2',
            'iso_alpha3' => 'string|nullable',
            'iso_numeric' => 'string|nullable',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_eu_country' => 'boolean',
        ];
    }
}
