<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle(),
            'description' => $this->faker->realText(),
            'due_date' => $this->faker->date(),
            'priority' => rand(0, 5),
            'time_budget_hours' => rand(0, 1000),
        ];
    }
}
