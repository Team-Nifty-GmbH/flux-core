<?php

namespace FluxErp\Rulesets\Product\ProductBundleProduct;

use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductBundleProductRuleset extends FluxRuleset
{
    protected static ?string $model = ProductBundleProduct::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductBundleProduct::class),
            ],
        ];
    }
}
