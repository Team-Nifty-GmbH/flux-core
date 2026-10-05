<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Lot;
use Illuminate\Database\Eloquent\Factories\Factory;

class LotFactory extends Factory
{
    protected $model = Lot::class;

    public function definition(): array
    {
        return [
            'lot_number' => fake()->unique()->bothify('LOT-#####'),
            'supplier_lot_number' => fake()->unique()->bothify('SUP-#####'),
            'produced_at' => fake()->dateTimeBetween('-6 months', '-1 month'),
            'expires_at' => fake()->dateTimeBetween('+1 month', '+2 years'),
            'description' => fake()->sentence(),
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => ['blocked_at' => now()]);
    }
}
