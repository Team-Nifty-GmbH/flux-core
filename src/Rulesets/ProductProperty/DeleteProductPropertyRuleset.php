<?php

namespace FluxErp\Rulesets\ProductProperty;

use FluxErp\Models\ProductProperty;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductPropertyRuleset extends FluxRuleset
{
    protected static ?string $model = ProductProperty::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductProperty::class),
            ],
        ];
    }
}
