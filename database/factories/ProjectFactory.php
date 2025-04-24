<?php

namespace FluxErp\Database\Factories;

use Carbon\Carbon;
use FluxErp\Models\Project;
use FluxErp\States\Project\ProjectState;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $from = Carbon::parse('2000-01-01 00:00:00');
        $to = Carbon::now();

        $state = ProjectState::all()->random()::$name;

        $startDate = Carbon::createFromTimestamp(rand($from->timestamp, $to->timestamp));
        $endDate = $state === 'done'
            ? Carbon::createFromTimestamp(rand($startDate->timestamp, $to->timestamp))->format('Y-m-d')
            : null;
        $startDate = $startDate->format('Y-m-d');

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
