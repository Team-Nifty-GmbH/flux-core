<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\RebateAgreement;
use Illuminate\Database\Eloquent\Factories\Factory;

class RebateAgreementFactory extends Factory
{
    protected $model = RebateAgreement::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'period_start' => now()->subYear()->startOfYear(),
            'period_end' => now()->subYear()->endOfYear(),
            'tiers' => [
                ['from_volume' => 50000, 'percentage' => 0.02],
                ['from_volume' => 100000, 'percentage' => 0.03],
            ],
            'is_active' => true,
        ];
    }
}
