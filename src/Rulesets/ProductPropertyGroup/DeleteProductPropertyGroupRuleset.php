<?php

namespace FluxErp\Rulesets\ProductPropertyGroup;

use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductPropertyGroupRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = ProductPropertyGroup::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductPropertyGroup::class]),
            ],
        ];
    }
}
