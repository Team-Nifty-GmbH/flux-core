<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Project;
use FluxErp\States\Project\ProjectState;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle(),
            'start_date' => $this->faker->date(),
            'description' => $this->faker->realText(),
            'state' => ProjectState::all()->random()::$name,
            'time_budget' => rand(0, 1000) . ':' . rand(0, 59),
            'budget' => $this->faker->randomFloat(),
        ];
    }
}
