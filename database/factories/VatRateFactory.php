<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

class VatRateFactory extends Factory
{
    protected $model = VatRate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Standard', 'Special', 'Reduced']),
            'rate_percentage' => fake()->randomElement([0.19, 0.16, 0.07]),
        ];
    }
}
