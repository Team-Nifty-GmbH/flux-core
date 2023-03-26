<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(90),
            'is_hidden' => $this->faker->boolean(10),
        ];
    }
}
