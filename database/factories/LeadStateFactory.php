<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\LeadState;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadStateFactory extends Factory
{
    protected $model = LeadState::class;

    public function definition(): array
    {
        $isWonLost = $this->faker->numberBetween(0, 3);

        return [
            'name' => $this->faker->name(),
            'probability_percentage' => $this->faker->boolean()
                ? $this->faker->randomFloat(2, 0, 1)
                : null,
            'color' => $this->faker->hexColor(),
            'is_won' => $isWonLost === 1,
            'is_lost' => $isWonLost === 2,
        ];
    }
}
