<?php

namespace FluxErp\Http\Requests;

class CreateCountryRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:countries,uuid',
            'language_id' => 'required|integer|exists:languages,id,deleted_at,NULL',
            'currency_id' => 'required|integer|exists:currencies,id,deleted_at,NULL',
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
