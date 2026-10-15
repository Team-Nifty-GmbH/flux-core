<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Product;
use FluxErp\Models\ProductConfigurationGroup;
use FluxErp\Models\ProductConfigurationGroupItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductConfigurationGroupItemFactory extends Factory
{
    protected $model = ProductConfigurationGroupItem::class;

    public function definition(): array
    {
        return [
            'product_configuration_group_id' => ProductConfigurationGroup::factory(),
            'product_id' => Product::factory()->state(['is_bundle' => false]),
            'surcharge' => 0,
            'max_amount' => 1,
            'sort' => 0,
        ];
    }
}
