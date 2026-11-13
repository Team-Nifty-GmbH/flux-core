<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\DepreciationMethodEnum;
use FluxErp\Models\FixedAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

class FixedAssetFactory extends Factory
{
    protected $model = FixedAsset::class;

    public function definition(): array
    {
        $acquiredAt = fake()->date();
        $acquisitionCost = fake()->randomFloat(2, 1000, 100000);

        return [
            'name' => fake()->words(3, true),
            'number' => fake()->bothify('FA-####'),
            'acquired_at' => $acquiredAt,
            'depreciation_starts_at' => $acquiredAt,
            'acquisition_cost' => $acquisitionCost,
            'residual_value' => 0,
            'book_value' => $acquisitionCost,
            'depreciation_method_enum' => DepreciationMethodEnum::Linear,
            'useful_life_months' => fake()->numberBetween(12, 120),
        ];
    }
}
