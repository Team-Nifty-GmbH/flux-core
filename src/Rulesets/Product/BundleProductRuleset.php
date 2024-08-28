<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class BundleProductRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'bundle_products' => 'exclude_unless:is_bundle,true|required_if:is_bundle,true|array',
            'bundle_products.*.id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'bundle_products.*.count' => 'required|numeric|min:0',
        ];
    }
}
