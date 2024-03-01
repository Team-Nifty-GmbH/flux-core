<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Category;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CategoryRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'categories' => 'array',
            'categories.*' => [
                'required',
                'integer',
                (new ModelExists(Category::class))
                    ->where('model_type', app(Product::class)->getMorphClass()),
            ],
        ];
    }
}
