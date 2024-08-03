<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\OrderPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderPosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => $this->faker->boolean() ?
                $this->faker->numberBetween(100, 11000) / 100 :
                $this->faker->numberBetween(1, 500),
            'discount_percentage' => $this->faker->boolean(80)
                ? null
                : $this->faker->randomFloat(4, 0, 1),
            'margin' => $this->faker->randomFloat(2),
            'provision' => $this->faker->numberBetween(1, 50000) / 100,
            'purchase_price' => $this->faker->numberBetween(1, 50000) / 100,
            'amount_packed_products' => $this->faker->boolean() ?
                0 :
                $this->faker->numberBetween(1, 50),
            'customer_delivery_date' => $this->faker->date(),
            'ean_code' => $this->faker->ean13(),
            'possible_delivery_date' => $this->faker->date(),
            'unit_gram_weight' => $this->faker->numberBetween(1, 999),

            'name' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraph(),

            'is_net' => $this->faker->boolean(90),
            'is_free_text' => $this->faker->boolean(15),
            'is_alternative' => $this->faker->boolean(15),
        ];
    }
}
