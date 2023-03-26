<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\StockPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockPostingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StockPosting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'stock' => $this->faker->numberBetween(1, 1000) / 10,
            'purchase_price' => $this->faker->numberBetween(1, 1000) / 10,
            'posting' => $this->faker->numberBetween(-500, 500),
            'description' => $this->faker->word,
        ];
    }
}
