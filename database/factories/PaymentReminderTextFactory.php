<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PaymentReminderText;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentReminderTextFactory extends Factory
{
    protected $model = PaymentReminderText::class;

    public function definition(): array
    {
        return [
            'reminder_subject' => fake()->sentence(),
            'reminder_body' => fake()->paragraph(),
            'reminder_level' => fake()->unique()->numberBetween(1, 10),
        ];
    }
}
