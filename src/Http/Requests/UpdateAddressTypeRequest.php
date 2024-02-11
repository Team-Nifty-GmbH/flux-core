<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\AddressType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\UniqueInFieldDependence;

class UpdateAddressTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(AddressType::class),
            ],
            'address_type_code' => [
                'string',
                'nullable',
                new UniqueInFieldDependence(AddressType::class, 'client_id'),
            ],
            'name' => 'sometimes|required|string',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }
}
