<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\Order;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateStockPostingsFromOrderRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'only_reserve_stock' => 'boolean',
        ];
    }
}
