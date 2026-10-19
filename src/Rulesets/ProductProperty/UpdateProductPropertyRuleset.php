<?php

namespace FluxErp\Rulesets\ProductProperty;

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateProductPropertyRuleset extends FluxRuleset
{
    protected static ?string $model = ProductProperty::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductProperty::class]),
            ],
            'product_property_group_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => ProductPropertyGroup::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'property_type_enum' => [
                'sometimes',
                'required',
                'string',
                Rule::enum(PropertyTypeEnum::class),
            ],
        ];
    }
}
