<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTypeFactory extends Factory
{
    protected $model = PaymentType::class;

    public function definition(): array
    {
        $default = $this->faker->boolean();

        return [
            'name' => $this->faker->firstName(),
            'description' => $this->faker->sentence(),
            'payment_reminder_days_1' => $this->faker->numberBetween(3, 23),
            'payment_reminder_days_2' => $this->faker->numberBetween(3, 23),
            'payment_reminder_days_3' => $this->faker->numberBetween(3, 23),
            'payment_target' => $paymentTarget = $this->faker->numberBetween(13, 42),
            'payment_discount_target' => $this->faker->numberBetween(0, $paymentTarget),
            'payment_discount_percentage' => $this->faker->numberBetween(10, 50) / 100,
            'is_active' => $default ?: $this->faker->boolean(90),
            'is_direct_debit' => $this->faker->boolean(),
            'is_default' => $default,
            'is_purchase' => $this->faker->boolean(),
            'is_sales' => $default ?: $this->faker->boolean(),
            'requires_manual_transfer' => $this->faker->boolean(),
        ];
    }
}
