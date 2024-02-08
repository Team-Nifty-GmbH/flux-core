<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Address;
use FluxErp\Rules\ModelExists;

class CreateWarehouseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:warehouses,uuid',
            'address_id' => [
                'integer',
                'nullable',
                new ModelExists(Address::class),
            ],
            'name' => 'required|string',
            'is_default' => 'boolean',
        ];
    }
}
