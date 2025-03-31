<?php

namespace FluxErp\Rulesets\ProductPropertyGroup;

use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductPropertyGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductPropertyGroup::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ProductPropertyRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:product_property_groups,uuid',
            'name' => 'required|string|max:255',
        ];
    }
}
