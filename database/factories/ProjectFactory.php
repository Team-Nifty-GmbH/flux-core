<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Project;
use FluxErp\States\Project\ProjectState;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $state = ProjectState::all()->random()::$name;
        $startDate = $this->faker->date();
        $endDate = $state === 'done'
            ? $this->faker->dateTimeBetween($startDate)->format('Y-m-d')
            : null;

        return [
            'name' => $this->faker->jobTitle(),
            'start_date' => $startDate,
            'description' => $this->faker->boolean() ? $this->faker->realText() : null,
            'end_date' => $endDate,
            'state' => $state,
            'progress' => $this->faker->randomFloat(2, 0, 1),
            'time_budget' => rand(0, 1000) . ':' . rand(0, 59),
            'budget' => $this->faker->randomFloat(10000),
            'total_cost' => $this->faker->randomFloat(10000),
        ];
    }
}
