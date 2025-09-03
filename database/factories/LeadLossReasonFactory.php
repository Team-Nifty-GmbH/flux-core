<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\LeadLossReason;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadLossReasonFactory extends Factory
{
    protected $model = LeadLossReason::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'is_active' => fake()->boolean(85),
        ];
    }
}
