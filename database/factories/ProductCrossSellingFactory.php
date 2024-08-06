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
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
