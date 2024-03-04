<?php

namespace FluxErp\Rulesets\ProductCrossSelling;

use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Sole;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductCrossSellingRuleset extends FluxRuleset
{
    protected static ?string $model = ProductCrossSelling::class;

    public function rules(): array
    {
        return [
            'uuid' => [
                'string',
                new Sole(ProductCrossSelling::class),
            ],
            'product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'name' => 'required|string|max:255',
            'order_column' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ProductRuleset::class, 'getRules')
        );
    }
}
