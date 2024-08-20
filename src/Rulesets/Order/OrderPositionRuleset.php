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
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'order_positions.*.amount' => [
                'required',
                app(Numeric::class, ['min' => 0]),
            ],
        ];
    }
}
