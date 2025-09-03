<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\StockPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockPostingFactory extends Factory
{
    protected $model = StockPosting::class;

    public function definition(): array
    {
        return [
            'stock' => fake()->numberBetween(1, 1000) / 10,
            'purchase_price' => fake()->numberBetween(1, 1000) / 10,
            'posting' => fake()->numberBetween(-500, 500),
            'description' => fake()->word,
        ];
    }
}
