<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Project;
use FluxErp\States\Project\ProjectState;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'project_name' => $this->faker->jobTitle(),
            'display_name' => $this->faker->jobTitle(),
            'release_date' => $this->faker->dateTime(),
            'state' => ProjectState::all()->random()::$name,
        ];
    }
}
