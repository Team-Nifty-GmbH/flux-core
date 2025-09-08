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
            'commission' => fake()->randomFloat(5, 0.01),
            'total_net_price' => fake()->randomFloat(2, 100, 10000),
            'commission_rate' => CommissionRateFactory::new()->make()->toArray(),
        ];
    }
}
