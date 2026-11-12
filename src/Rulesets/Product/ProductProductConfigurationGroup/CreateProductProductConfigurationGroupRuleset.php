<?php

namespace FluxErp\Rulesets\Product\ProductProductConfigurationGroup;

use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductProductConfigurationGroupRuleset extends FluxRuleset
{
    protected static ?string $model = ProductProductConfigurationGroup::class;

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->where('is_configurable', true),
            ],
            'product_configuration_group_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroup::class]),
            ],
            'is_required' => 'required|boolean',
            'sort' => 'required|integer|min:0',
        ];
    }
}
