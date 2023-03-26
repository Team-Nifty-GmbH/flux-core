<?php

namespace FluxErp\Http\Requests;

class CreateCountryRegionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country_id' => 'required|integer|exists:countries,id,deleted_at,NULL',
            'name' => 'required|string',
        ];
    }
}
