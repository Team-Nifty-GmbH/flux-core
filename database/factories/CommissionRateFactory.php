<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\CommissionRate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionRateFactory extends Factory
{
    protected $model = CommissionRate::class;

    public function definition(): array
    {
        return [
            'commission_rate' => $this->faker->randomFloat(min: 0.001, max: 0.9999),
        ];
    }
}
