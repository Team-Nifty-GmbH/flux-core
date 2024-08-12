<?php

namespace FluxErp\Rulesets\ProductProperty;

use FluxErp\Models\ProductProperty;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductPropertyRuleset extends FluxRuleset
{
    protected static ?string $model = ProductProperty::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:product_properties,uuid',
            'name' => 'required|string',
        ];
    }
}
