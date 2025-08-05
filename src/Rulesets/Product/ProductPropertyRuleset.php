<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\ProductProperty;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ProductPropertyRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'product_properties' => 'array',
            'product_properties.*.id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductProperty::class]),
            ],
            'product_properties.*.value' => 'string|max:255|nullable',
        ];
    }
}
