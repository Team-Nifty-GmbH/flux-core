<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\OrderPosition;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class OrderPositionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'order_positions' => 'nullable|array',
            'order_positions.*.id' => [
                'required',
                'integer',
                new ModelExists(OrderPosition::class),
            ],
            'order_positions.*.amount' => [
                'required',
                new Numeric(min: 0),
            ],
        ];
    }
}
