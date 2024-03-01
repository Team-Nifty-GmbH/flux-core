<?php

namespace FluxErp\Rulesets\Price;

use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdatePriceRuleset extends FluxRuleset
{
    protected static ?string $model = Price::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Price::class),
            ],
            'product_id' => [
                'integer',
                new ModelExists(Product::class),
            ],
            'price_list_id' => [
                'integer',
                new ModelExists(PriceList::class),
            ],
            'price' => 'sometimes|numeric',
        ];
    }
}
