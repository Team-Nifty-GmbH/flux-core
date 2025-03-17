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

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ProductRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => [
                'string',
                app(Sole::class, ['model' => ProductCrossSelling::class]),
            ],
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'name' => 'required|string|max:255',
            'order_column' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
