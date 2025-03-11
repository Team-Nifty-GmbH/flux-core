<?php

namespace FluxErp\Rulesets\ProductOption;

use FluxErp\Models\ProductOption;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductOptionRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = ProductOption::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductOption::class]),
            ],
        ];
    }
}
