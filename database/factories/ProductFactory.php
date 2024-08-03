<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'product_number' => $this->faker->word,
            'name' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraph(),
            'ean' => $this->faker->ean13(),
            'min_delivery_time' => $this->faker->numberBetween(3, 7),
            'max_delivery_time' => $this->faker->numberBetween(7, 15),
            'restock_time' => $this->faker->numberBetween(1, 30),
            'purchase_steps' => $this->faker->randomKey([1, 5, 10, 100, 15, 25, 50]),
            'min_purchase' => $this->faker->randomKey([1, 5, 10]),
            'max_purchase' => $this->faker->randomKey([100, 15, 25, 50]),
            'seo_keywords' => $this->faker->word,
            'manufacturer_product_number' => $this->faker->word,
            'posting_account' => $this->faker->word,
            'warning_stock_amount' => $this->faker->randomKey([100, 15, 25, 50]),
            'is_bundle' => $this->faker->boolean(30),
        ];
    }
}
