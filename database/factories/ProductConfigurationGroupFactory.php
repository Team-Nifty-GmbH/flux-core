<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\ProductConfigurationGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductConfigurationGroupFactory extends Factory
{
    protected $model = ProductConfigurationGroup::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Storage', 'Memory', 'Power supply', 'Graphics']),
            'selection_type_enum' => ConfigurationSelectionTypeEnum::Single,
            'price_mode_enum' => ConfigurationPriceModeEnum::Surcharge,
        ];
    }
}
