<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'price' => $this->faker->numberBetween(1, 5000) / 100,
        ];
    }
}
