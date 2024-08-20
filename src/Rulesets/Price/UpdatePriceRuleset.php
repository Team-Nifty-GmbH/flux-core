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
                app(ModelExists::class, ['model' => Price::class]),
            ],
            'product_id' => [
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'price_list_id' => [
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'price' => 'sometimes|numeric',
        ];
    }
}
