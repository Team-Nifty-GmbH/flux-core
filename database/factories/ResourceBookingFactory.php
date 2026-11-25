<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ResourceBooking;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ResourceBookingFactory extends Factory
{
    protected $model = ResourceBooking::class;

    public function definition(): array
    {
        $start = Carbon::parse(fake()->dateTimeBetween('-10 days', '+10 days'));

        return [
            'start' => $start,
            'end' => $start->clone()->addHours(rand(1, 8)),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
