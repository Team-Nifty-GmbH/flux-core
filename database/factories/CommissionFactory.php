<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Commission;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        return [
            'commission' => $this->faker->randomFloat(5, 0.01),
            'commission_rate' => $this->faker->randomFloat(min: 0.001, max: 0.9999),
            'total_net_price' => $this->faker->numberBetween(10, 1000),
        ];
    }
}
