<?php

namespace FluxErp\Rulesets\ProductOptionGroup;

use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductOptionGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductOptionGroup::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ProductOptionRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:product_option_groups,uuid',
            'name' => 'required|string',
        ];
    }
}
