<?php

namespace FluxErp\Rulesets\ProductPropertyGroup;

use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductPropertyGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductPropertyGroup::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductPropertyGroup::class),
            ],
        ];
    }
}
