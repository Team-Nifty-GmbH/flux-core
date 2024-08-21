<?php

namespace FluxErp\Rulesets\ProductOptionGroup;

use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductOptionGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductOptionGroup::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductOptionGroup::class]),
            ],
        ];
    }
}
