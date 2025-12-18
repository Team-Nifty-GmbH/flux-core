<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\LeadState;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadStateFactory extends Factory
{
    protected $model = LeadState::class;

    public function definition(): array
    {
        $isWonLost = fake()->numberBetween(0, 3);

        return [
            'name' => fake()->name(),
            'probability_percentage' => fake()->boolean()
                ? fake()->randomFloat(2, 0, 1)
                : null,
            'color' => fake()->hexColor(),
            'is_won' => $isWonLost === 1,
            'is_lost' => $isWonLost === 2,
        ];
    }
}
