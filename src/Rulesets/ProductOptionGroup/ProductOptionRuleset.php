<?php

namespace FluxErp\Rulesets\ProductOptionGroup;

use FluxErp\Rulesets\FluxRuleset;

class ProductOptionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'product_options' => 'array',
            'product_options.*.name' => 'required|string|max:255',
        ];
    }
}
