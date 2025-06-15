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
        $totalNet = bcmul($price, $amount, 2);
        $totalGross = net_to_gross($totalNet, 0.19);

        return [
            'name' => $this->faker->name,
            'amount' => $amount,
            'price' => $price,
            'total_net' => $totalNet,
            'total_gross' => $totalGross,
            'total' => $totalGross,
        ];
    }
}
