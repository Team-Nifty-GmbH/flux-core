<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'payment_target_days' => $paymentTargetDays = $this->faker->boolean
                ? $this->faker->numberBetween(int1: 1, int2: 30)
                : null,
            'payment_reminder_days_1' => $this->faker->numberBetween(int1: 1, int2: 30),
            'payment_reminder_days_2' => $this->faker->numberBetween(int1: 1, int2: 30),
            'payment_reminder_days_3' => $this->faker->numberBetween(int1: 1, int2: 30),
            'discount_days' => $paymentTargetDays
                ? $this->faker->numberBetween(int2: $paymentTargetDays)
                : null,
            'discount_percent' => $this->faker->boolean ? $this->faker->numberBetween(10, 50) / 100 : null,
            'credit_line' => $this->faker->boolean ?
                $this->faker->randomFloat(nbMaxDecimals: 2, min: 100, max: 10000) : null,
            'has_delivery_lock' => $this->faker->boolean,
            'has_sensitive_reminder' => $this->faker->boolean,
        ];
    }
}
