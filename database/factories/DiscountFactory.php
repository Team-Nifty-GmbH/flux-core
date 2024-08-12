<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        return [
            'discount' => $this->faker->numberBetween(1, 10000) / 100,
            'is_percentage' => $this->faker->boolean(),
        ];
    }
}
