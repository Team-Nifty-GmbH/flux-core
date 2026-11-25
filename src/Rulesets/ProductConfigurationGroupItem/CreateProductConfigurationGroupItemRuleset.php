<?php

namespace FluxErp\Rulesets\ProductConfigurationGroupItem;

use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateProductConfigurationGroupItemRuleset extends FluxRuleset
{
    protected static ?string $model = ProductConfigurationGroupItem::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:product_configuration_group_items,uuid',
            'product_configuration_group_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroup::class]),
            ],
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->where('is_configurable', false)
                    ->where('is_bundle', false)
                    ->where('is_variant_parent', false),
            ],
            'surcharge' => [
                'nullable',
                app(Numeric::class),
            ],
            'max_amount' => 'required|integer|min:1',
            'sort' => 'required|integer|min:0',
        ];
    }
}
