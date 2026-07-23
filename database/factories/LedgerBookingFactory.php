<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\LedgerBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

class LedgerBookingFactory extends Factory
{
    protected $model = LedgerBooking::class;

    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 100, 50000),
            'booking_date' => fake()->date(),
            'booking_text' => fake()->sentence(),
        ];
    }
}
