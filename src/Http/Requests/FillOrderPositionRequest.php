<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;

class FillOrderPositionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                new ModelExists(Order::class),
            ],
            'order_positions' => 'array',
            'simulate' => 'boolean',
        ];
    }
}
