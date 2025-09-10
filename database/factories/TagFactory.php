<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'type' => fake()->optional()->word(),
            'color' => fake()->optional()->hexColor(),
            'order_column' => fake()->optional()->numberBetween(1, 100),
        ];
    }
}
