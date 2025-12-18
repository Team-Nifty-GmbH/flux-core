<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ProductCrossSelling;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCrossSellingFactory extends Factory
{
    protected $model = ProductCrossSelling::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'is_active' => fake()->boolean(80),
        ];
    }
}
