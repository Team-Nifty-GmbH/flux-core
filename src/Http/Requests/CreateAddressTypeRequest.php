<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Client;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rule;

class CreateAddressTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:address_types,uuid',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'address_type_code' => [
                'string',
                'nullable',
                Rule::unique('address_types')->where('client_id', $data['client_id'] ?? null),
            ],
            'name' => 'required|string',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }
}
