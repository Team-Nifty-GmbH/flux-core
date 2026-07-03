<?php

namespace FluxErp\Rulesets\Resource;

use FluxErp\Models\Product;
use FluxErp\Models\Resource;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateResourceRuleset extends FluxRuleset
{
    protected static ?string $model = Resource::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(CategoryRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:resources,uuid',
            'product_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'name' => 'required|string|max:255',
            'resource_number' => 'nullable|string|max:255|unique:resources,resource_number',
            'description' => 'nullable|string',
            'allow_overbooking' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
