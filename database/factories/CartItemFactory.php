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
            'name' => fake()->name,
            'amount' => fake()->randomDigitNotZero(),
            'price' => fake()->randomFloat(2),
            'total_net' => fake()->randomFloat(2),
            'total_gross' => fake()->randomFloat(2),
            'total' => fake()->randomFloat(2),
        ];
    }
}
