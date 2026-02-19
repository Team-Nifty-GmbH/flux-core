<?php

namespace FluxErp\Rulesets\Product\ProductBundleProduct;

use FluxErp\Models\Pivots\BundleProductProduct;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductBundleProductRuleset extends FluxRuleset
{
    protected static ?string $model = BundleProductProduct::class;

    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => BundleProductProduct::class]),
            ],
            'bundle_product_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'count' => 'sometimes|required|numeric|gt:0',
        ];
    }
}
