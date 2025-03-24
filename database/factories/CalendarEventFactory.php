<?php

namespace FluxErp\Database\Factories;

use Carbon\Carbon;
use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        $startsAt = Carbon::createFromTimestamp(
            $this->faker->dateTimeBetween('-90 days', '+90 days')->getTimeStamp()
        );
        $endsAt = $startsAt->clone();
        $endsAt = $this->faker->boolean(15) ? $endsAt->addDays(rand(0, 5)) : null;

        return [
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->text(),
            'start' => $startsAt,
            'end' => $endsAt,
            'is_all_day' => $this->faker->boolean(),
        ];
    }
}
