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
            'start' => $start->format('Y-m-d'),
            'end' => $this->faker->dateTimeBetween($start, '+2 months')->format('Y-m-d'),
            'probability_percentage' => $this->faker->randomFloat(2, 0, 1),
            'expected_revenue' => $expectedRevenue = $this->faker->numberBetween(100, 90000),
            'expected_gross_profit' => $this->faker->numberBetween(0, $expectedRevenue),
            'score' => $this->faker->numberBetween(0, 5),
        ];
    }
}
