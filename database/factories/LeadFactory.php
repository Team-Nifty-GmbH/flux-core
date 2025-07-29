<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 year', '+2 months');

        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->realText(),
            'loss_reason' => $this->faker->realText(),
            'start' => $start->format('Y-m-d'),
            'end' => $this->faker->dateTimeBetween($start, '+2 months')->format('Y-m-d'),
            'probability_percentage' => $probability = $this->faker->randomFloat(2, 0, 1),
            'expected_revenue' => $expectedRevenue = $this->faker->numberBetween(100, 90000),
            'expected_gross_profit' => $expectedGrossProfit = $this->faker->numberBetween(0, $expectedRevenue),
            'score' => $this->faker->numberBetween(0, 5),
            'weighted_gross_profit' => bcmul($expectedGrossProfit, $probability),
            'weighted_revenue' => bcmul($expectedRevenue, $probability),
        ];
    }
}
