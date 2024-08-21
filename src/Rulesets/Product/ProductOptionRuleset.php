<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\ProductOption;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ProductOptionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'product_options' => 'array',
            'product_options.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductOption::class]),
            ],
        ];
    }
}
