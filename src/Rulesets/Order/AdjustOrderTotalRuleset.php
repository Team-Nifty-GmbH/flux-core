<?php

namespace FluxErp\Rulesets\Order;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class AdjustOrderTotalRuleset extends FluxRuleset
{
    protected static ?string $model = Order::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'total_gross_price' => [
                'required',
                app(Numeric::class, ['min' => 0.01]),
            ],
        ];
    }
}
