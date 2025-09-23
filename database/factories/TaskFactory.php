<?php

namespace FluxErp\Database\Factories;

use DateTime;
use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        /** @var DateTime $startDate */
        $startDate = fake()->dateTimeBetween(
            now()->subMonths(2)->startOfMonth(),
            now()->addMonths()->endOfMonth()
        );

        /** @var DateTime|null $dueDate */
        $dueDate = fake()->boolean(75)
            ? Carbon::instance($startDate)->addDays(rand(1, 3))->toDateTime()
            : null;

        return [
            'name' => fake()->jobTitle(),
            'description' => fake()->realText(),
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'start_timestamp' => $startDate->getTimestamp(),
            'due_date' => $dueDate?->format('Y-m-d H:i:s'),
            'due_timestamp' => $dueDate?->getTimestamp(),
            'start_time' => $startDate->format('H:i:s'),
            'due_time' => $dueDate?->format('H:i:s'),
            'priority' => rand(0, 5),
            'time_budget' => rand(0, 1000) . ':' . rand(0, 59),
            'budget' => fake()->randomFloat(),
        ];
    }
}
