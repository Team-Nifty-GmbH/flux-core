<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        $amount = $this->faker->randomDigitNotZero();
        $price = $this->faker->numberBetween(1, 1000);
        $total_net = bcmul($price, $amount, 2);
        $total_gross = net_to_gross($total_net, 0.19);

        return [
            'name' => $this->faker->boolean() ? $this->faker->name : null,
            'amount' => $amount,
            'price' => $price,
            'total_net' => $total_net,
            'total_gross' => $total_gross,
            'total' => $total_gross,
        ];
    }
}
