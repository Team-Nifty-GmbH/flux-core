<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle(),
            'sort_number' => 0,
        ];
    }
}
