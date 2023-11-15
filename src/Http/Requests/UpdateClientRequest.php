<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateClientRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:clients,id,deleted_at,NULL',
            'country_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('countries', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'sometimes|required|string',
            'client_code' => 'sometimes|required|string|unique:clients,client_code',
            'ceo' => 'string|nullable',
            'street' => 'string|nullable',
            'city' => 'string|nullable',
            'postcode' => 'string|nullable',
            'phone' => 'string|nullable',
            'fax' => 'string|nullable',
            'email' => 'email|nullable',
            'website' => 'string|nullable',
            'opening_hours' => 'array|nullable',
            'is_active' => 'boolean',
            'bank_connections' => 'array|nullable',
            'bank_connections.*' => [
                'integer',
                'exists:bank_connections,id',
            ],
        ];
    }
}
