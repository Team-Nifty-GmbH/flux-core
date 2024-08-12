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
            'name' => $this->faker->randomElement(['Standard', 'Special', 'Reduced']),
            'rate_percentage' => $this->faker->randomElement([0.19, 0.16, 0.07]),
        ];
    }
}
