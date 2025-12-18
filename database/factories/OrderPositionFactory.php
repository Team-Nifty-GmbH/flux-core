<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\OrderPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPositionFactory extends Factory
{
    protected $model = OrderPosition::class;

    public function definition(): array
    {
        return [
            'amount' => fake()->boolean() ?
                fake()->numberBetween(100, 11000) / 100 :
                fake()->numberBetween(1, 500),
            'discount_percentage' => fake()->boolean(80)
                ? null
                : fake()->randomFloat(4, 0, 1),
            'margin' => fake()->randomFloat(2),
            'provision' => fake()->numberBetween(1, 50000) / 100,
            'purchase_price' => fake()->numberBetween(1, 50000) / 100,
            'amount_packed_products' => fake()->boolean() ?
                0 :
                fake()->numberBetween(1, 50),
            'customer_delivery_date' => fake()->date(),
            'ean_code' => fake()->ean13(),
            'possible_delivery_date' => fake()->date(),
            'unit_gram_weight' => fake()->numberBetween(1, 999),

            'name' => fake()->jobTitle(),
            'description' => fake()->paragraph(),

            'is_net' => fake()->boolean(90),
            'is_free_text' => fake()->boolean(15),
            'is_alternative' => fake()->boolean(15),
        ];
    }
}
