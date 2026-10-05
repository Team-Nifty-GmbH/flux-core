<?php

namespace FluxErp\Rulesets\Resource;

use FluxErp\Models\Product;
use FluxErp\Models\Resource;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateResourceRuleset extends FluxRuleset
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
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Resource::class]),
            ],
            'product_id' => [
                'sometimes',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Product::class, 'subject' => Resource::class])
                    ->where('is_bundle', false)
                    ->where('is_variant_parent', false),
            ],
            'name' => 'sometimes|required|string|max:255',
            'resource_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => 'sometimes|nullable|string',
            'allow_overbooking' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
