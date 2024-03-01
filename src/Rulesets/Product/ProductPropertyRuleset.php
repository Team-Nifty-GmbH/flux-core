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
                new ModelExists(ProductProperty::class),
            ],
            'product_properties.*.value' => 'required|string',
        ];
    }
}
