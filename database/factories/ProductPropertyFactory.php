<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ProductProperty;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPropertyFactory extends Factory
{
    protected $model = ProductProperty::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['dimension_height', 'dimension_width', 'dimension_length', 'weight_grams']),
        ];
    }
}
