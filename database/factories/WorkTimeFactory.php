<?php

namespace FluxErp\Database\Factories;

use DateInterval;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkTimeFactory extends Factory
{
    protected $model = WorkTime::class;

    public function definition(): array
    {
        return [
            'started_at' => $startedAt = fake()->dateTimeBetween(
                now()->subMonths(2)->startOfMonth(),
                now()->addMonths()->endOfMonth()
            ),
            'ended_at' => $startedAt->add(DateInterval::createFromDateString('8 hours')),
            'name' => fake()->jobTitle(),
            'description' => fake()->realText(),
            'is_pause' => fake()->boolean(25),
            'is_billable' => fake()->boolean(75),
            'is_locked' => fake()->boolean(90),
        ];
    }
}
