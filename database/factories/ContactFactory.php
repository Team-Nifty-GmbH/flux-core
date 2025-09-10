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
            'payment_target_days' => $paymentTargetDays = fake()->boolean
                ? fake()->numberBetween(int1: 1, int2: 30)
                : null,
            'payment_reminder_days_1' => fake()->numberBetween(int1: 1, int2: 30),
            'payment_reminder_days_2' => fake()->numberBetween(int1: 1, int2: 30),
            'payment_reminder_days_3' => fake()->numberBetween(int1: 1, int2: 30),
            'discount_days' => $paymentTargetDays
                ? fake()->numberBetween(int2: $paymentTargetDays)
                : null,
            'discount_percent' => fake()->boolean ? fake()->numberBetween(10, 50) / 100 : null,
            'credit_line' => fake()->boolean ?
                fake()->randomFloat(nbMaxDecimals: 2, min: 100, max: 10000) : null,
            'has_delivery_lock' => fake()->boolean,
            'has_sensitive_reminder' => fake()->boolean,
        ];
    }
}
