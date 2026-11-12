<?php

namespace FluxErp\Rulesets\ProductConfigurationGroupItem;

use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateProductConfigurationGroupItemRuleset extends FluxRuleset
{
    protected static ?string $model = ProductConfigurationGroupItem::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroupItem::class]),
            ],
            'product_id' => [
                'sometimes',
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
            'max_amount' => 'sometimes|required|integer|min:1',
            'sort' => 'sometimes|required|integer|min:0',
        ];
    }
}
