<?php

namespace FluxErp\Rulesets\Product\Variant;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rulesets\Product\CreateProductRuleset;
use FluxErp\Rulesets\Product\ProductOptionRuleset;

class CreateVariantsRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'parent_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            resolve_static(CreateProductRuleset::class, 'getRules'),
            parent::getRules(),
            resolve_static(ProductOptionRuleset::class, 'getRules'),
            ['product_options' => 'required|array'],
        );
    }
}
