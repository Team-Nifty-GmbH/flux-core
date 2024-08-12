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
            'payment_target' => $this->faker->numberBetween(10, 20),
            'payment_discount_target' => $this->faker->numberBetween(3, 5),
            'payment_discount_percent' => $this->faker->numberBetween(1, 10) / 100,
            'header_discount' => $this->faker->numberBetween(1, 10),
            'shipping_costs_net_price' => $this->faker->numberBetween(1, 5),
            'number_of_packages' => $this->faker->numberBetween(1, 100),
            'payment_reminder_days_1' => $this->faker->numberBetween(1, 10),
            'payment_reminder_days_2' => $this->faker->numberBetween(1, 10),
            'payment_reminder_days_3' => $this->faker->numberBetween(1, 10),
            'commission' => $this->faker->text(20),

            'header' => $this->faker->text(500),
            'footer' => $this->faker->text(500),
            'logistic_note' => $this->faker->text(100),
            'tracking_email' => $this->faker->email(),
            'payment_texts' => [$this->faker->text(300)],

            'order_date' => $this->faker->date(),
            'system_delivery_date' => $this->faker->date(),
            'customer_delivery_date' => $this->faker->date(),
            'date_of_approval' => $this->faker->date(),

            'has_logistic_notify_phone_number' => $this->faker->boolean(),
            'has_logistic_notify_number' => $this->faker->boolean(),
            'is_locked' => $this->faker->boolean(70),
            'is_new_customer' => $this->faker->boolean(),
            'is_imported' => $this->faker->boolean(),
            'is_merge_invoice' => $this->faker->boolean(),
            'is_confirmed' => $this->faker->boolean(),
            'is_paid' => $this->faker->boolean(),
            'requires_approval' => $this->faker->boolean(),
        ];
    }
}
