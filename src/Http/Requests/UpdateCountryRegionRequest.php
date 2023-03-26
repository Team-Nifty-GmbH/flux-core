<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateCountryRegionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:country_regions,id,deleted_at,NULL',
            'country_id' => [
                'integer',
                (new ExistsWithIgnore('countries', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'string',
        ];
    }
}
