<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'session_id' => fake()->uuid,
            'name' => fake()->name,
            'is_portal_public' => fake()->boolean,
            'is_public' => fake()->boolean,
            'is_watchlist' => fake()->boolean,
        ];
    }
}
