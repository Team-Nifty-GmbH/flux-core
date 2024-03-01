<?php

namespace FluxErp\Rulesets\ProductProperty;

use FluxErp\Models\ProductProperty;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductPropertyRuleset extends FluxRuleset
{
    protected static ?string $model = ProductProperty::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductProperty::class),
            ],
            'name' => 'required|string',
        ];
    }
}
