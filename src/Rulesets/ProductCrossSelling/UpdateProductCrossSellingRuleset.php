<?php

namespace FluxErp\Rulesets\ProductCrossSelling;

use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductCrossSellingRuleset extends FluxRuleset
{
    protected static ?string $model = ProductCrossSelling::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductCrossSelling::class),
            ],
            'product_id' => [
                'integer',
                'nullable',
                new ModelExists(Product::class),
            ],
            'name' => 'sometimes|required|string|max:255',
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
