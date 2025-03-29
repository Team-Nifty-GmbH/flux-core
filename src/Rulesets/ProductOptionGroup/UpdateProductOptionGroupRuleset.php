<?php

namespace FluxErp\Rulesets\ProductOptionGroup;

use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductOptionGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductOptionGroup::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ProductOptionRuleset::class, 'getRules'),
            [
                'product_options.*.id' => [
                    'sometimes',
                    'required',
                    'integer',
                    app(ModelExists::class, ['model' => ProductOption::class]),
                ],
            ]
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductOptionGroup::class]),
            ],
            'name' => 'required|string|max:255',
        ];
    }
}
