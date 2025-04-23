<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ProductPropertyGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPropertyGroupFactory extends Factory
{
    protected $model = ProductPropertyGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
