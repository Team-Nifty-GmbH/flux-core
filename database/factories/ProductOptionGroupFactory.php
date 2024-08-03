<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionGroupFactory extends Factory
{
    protected $model = ProductOptionGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['color', 'material', 'size', 'gender']),
        ];
    }
}
