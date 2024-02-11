<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Address;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;

class CreateSerialNumberRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:serial_numbers,uuid',
            'serial_number_range_id' => [
                'integer',
                new ModelExists(SerialNumberRange::class),
            ],
            'product_id' => [
                'integer',
                new ModelExists(Product::class),
            ],
            'address_id' => [
                'integer',
                new ModelExists(Address::class),
            ],
            'order_position_id' => [
                'integer',
                new ModelExists(OrderPosition::class),
            ],
            'serial_number' => 'required|string',
        ];
    }
}
