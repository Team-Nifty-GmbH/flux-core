<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

    public function definition(): array
    {
        return [
            'name' => fake()->colorName(),
        ];
    }
}
