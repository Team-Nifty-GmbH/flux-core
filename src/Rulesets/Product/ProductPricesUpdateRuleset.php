<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class ProductPricesUpdateRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'price_list_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'base_price_list_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
            'alternation' => [
                'required',
                app(Numeric::class),
            ],
            'is_percent' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ProductsRuleset::class, 'getRules'),
            resolve_static(RoundingRuleset::class, 'getRules')
        );
    }
}
