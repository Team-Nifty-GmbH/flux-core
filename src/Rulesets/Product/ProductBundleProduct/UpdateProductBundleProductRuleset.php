<?php

namespace FluxErp\Rulesets\Product\ProductBundleProduct;

use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductBundleProductRuleset extends FluxRuleset
{
    protected static ?string $model = ProductBundleProduct::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductBundleProduct::class),
            ],
            'bundle_product_id' => [
                'sometimes',
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'count' => 'sometimes|required|numeric|gt:0',
        ];
    }
}
