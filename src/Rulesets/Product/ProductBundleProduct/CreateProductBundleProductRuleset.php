<?php

namespace FluxErp\Rulesets\Product\ProductBundleProduct;

use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductBundleProductRuleset extends FluxRuleset
{
    protected static ?string $model = ProductBundleProduct::class;

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'bundle_product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'count' => 'required|numeric|gt:0',
        ];
    }
}
