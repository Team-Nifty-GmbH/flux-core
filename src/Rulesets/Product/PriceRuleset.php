<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class PriceRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'prices' => 'array',
            'prices.*.price_list_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'prices.*.price' => 'required|numeric',
        ];
    }
}
