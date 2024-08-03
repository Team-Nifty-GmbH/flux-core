<?php

namespace FluxErp\Database\Factories;

use DateInterval;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkTimeFactory extends Factory
{
    protected $model = WorkTime::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'started_at' => $startedAt = $this->faker->dateTimeBetween(
                now()->subMonths(2)->startOfMonth(),
                now()->addMonths()->endOfMonth()
            ),
            'ended_at' => $startedAt->add(DateInterval::createFromDateString('8 hours')),
            'name' => $this->faker->jobTitle(),
            'description' => $this->faker->realText(),
            'is_pause' => $this->faker->boolean(25),
            'is_billable' => $this->faker->boolean(75),
            'is_locked' => $this->faker->boolean(90),
        ];
    }
}
