<?php

namespace FluxErp\Rulesets\Product\ProductBundleProduct;

use FluxErp\Models\Pivots\BundleProductProduct;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductBundleProductRuleset extends FluxRuleset
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
        ];
    }
}
