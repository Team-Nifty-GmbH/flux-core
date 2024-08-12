<?php

namespace FluxErp\Rulesets\SerialNumber;

use FluxErp\Models\Address;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateSerialNumberRuleset extends FluxRuleset
{
    protected static ?string $model = SerialNumber::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:serial_numbers,uuid',
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
