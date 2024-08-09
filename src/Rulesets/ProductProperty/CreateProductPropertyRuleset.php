<?php

namespace FluxErp\Rulesets\ProductProperty;

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateProductPropertyRuleset extends FluxRuleset
{
    protected static ?string $model = ProductProperty::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:product_properties,uuid',
            'product_property_group_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => ProductPropertyGroup::class]),
            ],
            'name' => 'required|string',
            'property_type_enum' => [
                'required',
                'string',
                Rule::enum(PropertyTypeEnum::class),
            ],
        ];
    }
}
