<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTypeFactory extends Factory
{
    protected $model = PaymentType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'description' => $this->faker->sentence(),
            'payment_reminder_days_1' => $this->faker->numberBetween(3, 23),
            'payment_reminder_days_2' => $this->faker->numberBetween(3, 23),
            'payment_reminder_days_3' => $this->faker->numberBetween(3, 23),
            'payment_target' => $paymentTarget = $this->faker->numberBetween(13, 42),
            'payment_discount_target' => $this->faker->numberBetween(0, $paymentTarget),
            'payment_discount_percentage' => $this->faker->numberBetween(10, 50),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
