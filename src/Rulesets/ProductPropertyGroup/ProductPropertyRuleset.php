<?php

namespace FluxErp\Rulesets\ProductPropertyGroup;

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class ProductPropertyRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'product_properties' => 'array',
            'product_properties.*.name' => 'required|string|max:255',
            'product_properties.*.property_type_enum' => [
                'sometimes',
                'required',
                'string',
                Rule::enum(PropertyTypeEnum::class),
            ],
        ];
    }
}
