<?php

namespace FluxErp\Rulesets\ProductProperty;

use FluxErp\Models\ProductProperty;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductPropertyRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = ProductProperty::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductProperty::class]),
            ],
        ];
    }
}
