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
            'name' => $this->faker->unique()->word(),
            'slug' => $this->faker->unique()->slug(),
            'type' => $this->faker->optional()->word(),
            'color' => $this->faker->optional()->hexColor(),
            'order_column' => $this->faker->optional()->numberBetween(1, 100),
        ];
    }
}
