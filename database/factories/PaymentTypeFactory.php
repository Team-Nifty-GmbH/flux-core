<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTypeFactory extends Factory
{
    protected $model = PaymentType::class;

    public function definition(): array
    {
        $default = fake()->boolean();

        return [
            'name' => fake()->firstName(),
            'description' => fake()->sentence(),
            'payment_reminder_days_1' => fake()->numberBetween(3, 23),
            'payment_reminder_days_2' => fake()->numberBetween(3, 23),
            'payment_reminder_days_3' => fake()->numberBetween(3, 23),
            'payment_target' => $paymentTarget = fake()->numberBetween(13, 42),
            'payment_discount_target' => fake()->numberBetween(0, $paymentTarget),
            'payment_discount_percentage' => fake()->numberBetween(10, 50) / 100,
            'is_active' => $default ?: fake()->boolean(90),
            'is_direct_debit' => fake()->boolean(),
            'is_default' => $default,
            'is_purchase' => fake()->boolean(),
            'is_sales' => $default ?: fake()->boolean(),
            'requires_manual_transfer' => fake()->boolean(),
        ];
    }
}
