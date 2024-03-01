<?php

namespace FluxErp\Rulesets\ProductOption;

use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductOptionRuleset extends FluxRuleset
{
    protected static ?string $model = ProductOption::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductOption::class),
            ],
            'product_option_group_id' => [
                'integer',
                new ModelExists(ProductOptionGroup::class),
            ],
            'name' => 'sometimes|required|string',
        ];
    }
}
