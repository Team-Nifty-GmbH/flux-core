<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationDefault;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class SyncProductConfigurationDefaultsRuleset extends FluxRuleset
{
    protected static ?string $model = ProductConfigurationDefault::class;

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->where('is_configurable', true),
            ],
            'defaults' => 'present|array',
            'defaults.*.product_configuration_group_item_id' => 'required|integer',
            'defaults.*.amount' => 'required|integer|min:1',
        ];
    }
}
