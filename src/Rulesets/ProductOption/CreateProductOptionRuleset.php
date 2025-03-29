<?php

namespace FluxErp\Rulesets\ProductOption;

use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductOptionRuleset extends FluxRuleset
{
    protected static ?string $model = ProductOption::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:product_options,uuid',
            'product_option_group_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductOptionGroup::class]),
            ],
            'name' => 'required|string|max:255',
        ];
    }
}
