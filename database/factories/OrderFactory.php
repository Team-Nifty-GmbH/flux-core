<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'payment_target' => fake()->numberBetween(10, 20),
            'payment_discount_target' => fake()->numberBetween(3, 5),
            'payment_discount_percent' => fake()->numberBetween(1, 10) / 100,
            'header_discount' => fake()->numberBetween(1, 10),
            'shipping_costs_net_price' => fake()->numberBetween(1, 5),
            'number_of_packages' => fake()->numberBetween(1, 100),
            'payment_reminder_days_1' => fake()->numberBetween(1, 10),
            'payment_reminder_days_2' => fake()->numberBetween(1, 10),
            'payment_reminder_days_3' => fake()->numberBetween(1, 10),
            'commission' => fake()->text(20),

            'header' => fake()->text(500),
            'footer' => fake()->text(500),
            'logistic_note' => fake()->text(100),
            'tracking_email' => fake()->email(),
            'payment_texts' => [fake()->text(300)],

            'order_date' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'system_delivery_date' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'customer_delivery_date' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'date_of_approval' => fake()->dateTimeBetween('-1 year', '+1 year'),

            'has_logistic_notify_phone_number' => fake()->boolean(),
            'has_logistic_notify_number' => fake()->boolean(),
            'is_locked' => fake()->boolean(70),
            'is_new_customer' => fake()->boolean(),
            'is_imported' => fake()->boolean(),
            'is_merge_invoice' => fake()->boolean(),
            'is_confirmed' => fake()->boolean(),
            'is_paid' => fake()->boolean(),
            'requires_approval' => fake()->boolean(),
        ];
    }
}
