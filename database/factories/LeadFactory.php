<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 year', '+2 months');

        return [
            'name' => fake()->name(),
            'description' => fake()->realText(),
            'loss_reason' => fake()->realText(),
            'start' => $start->format('Y-m-d'),
            'end' => fake()->dateTimeBetween($start, '+2 months')->format('Y-m-d'),
            'probability_percentage' => fake()->randomFloat(2, 0, 1),
            'expected_revenue' => $expectedRevenue = fake()->numberBetween(100, 90000),
            'expected_gross_profit' => fake()->numberBetween(0, $expectedRevenue),
            'score' => fake()->numberBetween(0, 5),
        ];
    }
}
