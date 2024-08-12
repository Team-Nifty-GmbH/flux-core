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
            'stock' => $this->faker->numberBetween(1, 1000) / 10,
            'purchase_price' => $this->faker->numberBetween(1, 1000) / 10,
            'posting' => $this->faker->numberBetween(-500, 500),
            'description' => $this->faker->word,
        ];
    }
}
