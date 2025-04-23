<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $cronOptions = [
            '0 2 * * *',
            '30 1 * * 0',
            '*/15 * * * *',
            '0 6 * * 1-5',
            '0 0 1 * *',
        ];

        $chosenCron = $this->faker->randomElement($cronOptions);

        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'class' => $this->faker->name(),
            'type' => $this->faker->randomElement(RepeatableTypeEnum::values()),
            'description' => $this->faker->text(),
            'cron' => $chosenCron,
            'cron_expression' => $chosenCron,
            'due_at' => now()->addMinutes(fake()->numberBetween(10, 1440)),
            'ends_at' => now()->addMonths(fake()->numberBetween(1, 6)),
            'recurrences' => fake()->numberBetween(10, 200),
            'current_recurrence' => 0,
            'is_active' => true,
        ];
    }
}
