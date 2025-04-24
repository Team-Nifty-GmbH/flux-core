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
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
            'color' => $this->faker->colorName,
            'order_column' => $this->faker->numberBetween(1, 50),
        ];
    }
}
