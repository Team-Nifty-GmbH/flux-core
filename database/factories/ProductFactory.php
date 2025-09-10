<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_number' => fake()->word,
            'name' => fake()->jobTitle(),
            'description' => fake()->paragraph(),
            'ean' => fake()->ean13(),
            'min_delivery_time' => fake()->numberBetween(3, 7),
            'max_delivery_time' => fake()->numberBetween(7, 15),
            'restock_time' => fake()->numberBetween(1, 30),
            'purchase_steps' => fake()->randomKey([1, 5, 10, 100, 15, 25, 50]),
            'min_purchase' => fake()->randomKey([1, 5, 10]),
            'max_purchase' => fake()->randomKey([100, 15, 25, 50]),
            'seo_keywords' => fake()->word,
            'manufacturer_product_number' => fake()->word,
            'posting_account' => fake()->word,
            'warning_stock_amount' => fake()->randomKey([100, 15, 25, 50]),
            'is_bundle' => fake()->boolean(30),
        ];
    }
}
