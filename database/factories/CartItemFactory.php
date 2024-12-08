<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'amount' => $this->faker->randomDigitNotZero(),
            'price' => $this->faker->randomFloat(2),
            'total_net' => $this->faker->randomFloat(2),
            'total_gross' => $this->faker->randomFloat(2),
            'total' => $this->faker->randomFloat(2),
        ];
    }
}
