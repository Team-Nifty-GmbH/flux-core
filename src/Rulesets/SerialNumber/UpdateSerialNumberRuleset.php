<?php

namespace FluxErp\Rulesets\SerialNumber;

use FluxErp\Models\Address;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateSerialNumberRuleset extends FluxRuleset
{
    protected static ?string $model = SerialNumber::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(SerialNumber::class),
            ],
            'product_id' => [
                'integer',
                'nullable',
                new ModelExists(Product::class),
            ],
            'address_id' => [
                'integer',
                'nullable',
                new ModelExists(Address::class),
            ],
            'order_position_id' => [
                'integer',
                'nullable',
                new ModelExists(OrderPosition::class),
            ],
            'serial_number' => 'sometimes|required|string',
        ];
    }
}
