<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ProductsRuleset extends FluxRuleset
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
