<?php

namespace FluxErp\Rulesets\Product\ProductBundleProduct;

use FluxErp\Models\Pivots\BundleProductProduct;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductBundleProductRuleset extends FluxRuleset
{
    protected static ?string $model = BundleProductProduct::class;

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'bundle_product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'count' => 'required|numeric|gt:0',
        ];
    }
}
