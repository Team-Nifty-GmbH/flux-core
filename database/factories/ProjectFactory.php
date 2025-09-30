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
        return [
            'name' => fake()->jobTitle(),
            'start_date' => fake()->date(),
            'description' => fake()->realText(),
            'state' => ProjectState::all()->random()::$name,
            'time_budget' => rand(0, 1000) . ':' . rand(0, 59),
            'budget' => fake()->randomFloat(),
        ];
    }
}
