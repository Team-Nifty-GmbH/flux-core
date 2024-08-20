<?php

namespace FluxErp\Rulesets\Price;

use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreatePriceRuleset extends FluxRuleset
{
    protected static ?string $model = Price::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:prices,uuid',
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'price_list_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'price' => 'required|numeric',
        ];
    }
}
