<?php

namespace FluxErp\Database\Factories;

use DateInterval;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkTimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkTime::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'started_at' => $startedAt = $this->faker->dateTimeThisYear(),
            'ended_at' => $startedAt->add(DateInterval::createFromDateString('8 hours')),
            'description' => $this->faker->realText(),
            'is_pause' => $this->faker->boolean(25),
        ];
    }
}
