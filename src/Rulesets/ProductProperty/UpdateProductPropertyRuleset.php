<?php

namespace FluxErp\Rulesets\ProductProperty;

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Models\ProductProperty;
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
            'name' => 'sometimes|required|string',
            'property_type_enum' => [
                'sometimes',
                'required',
                'string',
                Rule::enum(PropertyTypeEnum::class),
            ],
        ];
    }
}
