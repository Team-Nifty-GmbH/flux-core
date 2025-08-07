<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\WorkTimeModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkTimeModelFactory extends Factory
{
    protected $model = WorkTimeModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Vollzeit', 'Teilzeit 80%', 'Teilzeit 60%', 'Teilzeit 50%']),
            'cycle_weeks' => $this->faker->randomElement([1, 2, 4]),
            'weekly_hours' => $this->faker->randomElement([40, 35, 30, 20]),
            'annual_vacation_days' => $this->faker->randomElement([30, 28, 25, 20]),
            'max_overtime_hours' => $this->faker->randomElement([100, 150, 200]),
            'overtime_compensation' => $this->faker->randomElement(['payment', 'time_off', 'both']),
            'is_active' => true,
            'client_id' => 1,
        ];
    }
}