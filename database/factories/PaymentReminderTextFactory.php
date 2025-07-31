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
            'reminder_subject' => $this->faker->sentence(),
            'reminder_body' => $this->faker->paragraph(),
            'reminder_level' => $this->faker->unique()->numberBetween(1, 10),
        ];
    }
}
