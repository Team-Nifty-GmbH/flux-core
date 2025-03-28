<?php

namespace FluxErp\Rulesets\OrderPosition;

use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class FillOrderPositionsRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = OrderPosition::class;

    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'order_positions' => 'array',
            'simulate' => 'boolean',
        ];
    }
}
