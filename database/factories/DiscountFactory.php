<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'discount' => $this->faker->numberBetween(1, 10000) / 100,
            'is_percentage' => $this->faker->boolean(),
        ];
    }
}
