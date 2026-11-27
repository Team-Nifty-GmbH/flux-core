<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\FixedAssetDepreciation;
use Illuminate\Database\Eloquent\Factories\Factory;

class FixedAssetDepreciationFactory extends Factory
{
    protected $model = FixedAssetDepreciation::class;

    public function definition(): array
    {
        return [
            'sequence' => fake()->numberBetween(1, 120),
            'booking_date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'book_value_after' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
