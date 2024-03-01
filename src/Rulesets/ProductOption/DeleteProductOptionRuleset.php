<?php

namespace FluxErp\Rulesets\ProductOption;

use FluxErp\Models\ProductOption;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductOptionRuleset extends FluxRuleset
{
    protected static ?string $model = ProductOption::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductOption::class),
            ],
        ];
    }
}
