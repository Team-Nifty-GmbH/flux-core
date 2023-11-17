<?php

namespace FluxErp\Http\Requests;

class CreateClientRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:clients,uuid',
            'country_id' => 'required|integer|exists:countries,id,deleted_at,NULL',
            'name' => 'required|string',
            'client_code' => 'required|string|unique:clients,client_code',
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
            'bank_connections.*' => 'integer|exists:bank_connections,id',
        ];
    }
}
