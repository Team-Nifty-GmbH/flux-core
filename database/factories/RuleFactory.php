<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;

class RuleFactory extends Factory
{
    protected $model = Rule::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'priority' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
