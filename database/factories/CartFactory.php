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
            'session_id' => $this->faker->uuid,
            'name' => $this->faker->name,
            'total' => $this->faker->boolean() ? $this->faker->numberBetween(10, 1000) : null,
            'is_portal_public' => $this->faker->boolean,
            'is_public' => $this->faker->boolean,
            'is_watchlist' => $this->faker->boolean,
        ];
    }
}
