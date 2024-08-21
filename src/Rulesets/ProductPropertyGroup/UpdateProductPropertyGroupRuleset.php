<?php

namespace FluxErp\Rulesets\ProductPropertyGroup;

use FluxErp\Models\ProductProperty;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductPropertyGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductPropertyGroup::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductPropertyGroup::class]),
            ],
            'name' => 'required|string',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(ProductPropertyRuleset::class, 'getRules'),
            [
                'product_properties.*.id' => [
                    'sometimes',
                    'required',
                    'integer',
                    app(ModelExists::class, ['model' => ProductProperty::class]),
                ],
            ]
        );
    }
}
