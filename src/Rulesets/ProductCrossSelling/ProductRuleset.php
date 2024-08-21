<?php

namespace FluxErp\Rulesets\ProductCrossSelling;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ProductRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'products' => 'array',
            'products.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
        ];
    }
}
