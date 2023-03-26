<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FluxErp\Models\CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CalendarEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startsAt = Carbon::createFromTimestamp($this->faker->dateTimeBetween('-90 days', '+90 days')->getTimeStamp());

        return [
            'title' => $this->faker->jobTitle(),
            'subtitle' => $this->faker->jobTitle(),
            'description' => $this->faker->text(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->clone()->addDays(rand(0, 5)),
            'is_all_day' => $this->faker->boolean(),
        ];
    }
}
