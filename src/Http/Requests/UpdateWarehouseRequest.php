<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Address;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;

class UpdateWarehouseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Warehouse::class),
            ],
            'address_id' => [
                'integer',
                'nullable',
                new ModelExists(Address::class),
            ],
            'name' => 'sometimes|required|string',
            'is_default' => 'boolean',
        ];
    }
}
