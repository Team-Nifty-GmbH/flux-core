<?php

namespace FluxErp\Rulesets\Resource;

use FluxErp\Models\Product;
use FluxErp\Models\Resource;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateResourceRuleset extends FluxRuleset
{
    protected static ?string $model = Resource::class;

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
                app(ModelExists::class, ['model' => Product::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'resource_number' => 'sometimes|nullable|string|max:255|unique:resources,resource_number',
            'allow_overbooking' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string',
            'categories' => 'sometimes|array|nullable',
            'categories.*' => 'integer',
        ];
    }
}
