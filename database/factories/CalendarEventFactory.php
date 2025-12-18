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
            fake()->dateTimeBetween('-90 days', '+90 days')->getTimeStamp()
        );
        $endsAt = $startsAt->clone();
        $endsAt = fake()->boolean(15) ? $endsAt->addDays(rand(0, 5)) : null;

        return [
            'title' => fake()->jobTitle(),
            'description' => fake()->text(),
            'start' => $startsAt,
            'end' => $endsAt,
            'is_all_day' => fake()->boolean(),
        ];
    }
}
