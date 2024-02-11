<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Country;
use FluxErp\Rules\ModelExists;

class CreateClientRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:clients,uuid',
            'country_id' => [
                'nullable',
                'integer',
                new ModelExists(Country::class),
            ],
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
            'creditor_identifier' => 'string|nullable',
            'sepa_text' => 'string|nullable',
            'opening_hours' => 'array|nullable',
            'terms_and_conditions' => 'string|nullable',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'bank_connections' => 'array|nullable',
            'bank_connections.*' => [
                'integer',
                new ModelExists(BankConnection::class),
            ],
        ];
    }
}
