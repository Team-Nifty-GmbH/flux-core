<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\ProductConfigurationRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductConfigurationRuleFactory extends Factory
{
    protected $model = ProductConfigurationRule::class;

    public function definition(): array
    {
        return [
            'product_configuration_group_item_id' => ProductConfigurationGroupItem::factory(),
            'rule_type_enum' => ConfigurationRuleTypeEnum::Requires,
        ];
    }
}
