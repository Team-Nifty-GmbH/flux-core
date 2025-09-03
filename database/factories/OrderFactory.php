<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PriceList;
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
            'shipping_costs_net_price' => fake()->numberBetween(1, 5),
            'payment_reminder_days_1' => fake()->numberBetween(1, 10),
            'payment_reminder_days_2' => fake()->numberBetween(1, 10),
            'payment_reminder_days_3' => fake()->numberBetween(1, 10),
            'commission' => fake()->text(20),

            'header' => fake()->text(500),
            'footer' => fake()->text(500),
            'logistic_note' => fake()->text(100),

            'order_date' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'system_delivery_date' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'customer_delivery_date' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'date_of_approval' => fake()->dateTimeBetween('-1 year', '+1 year'),

            'is_locked' => fake()->boolean(70),
            'is_imported' => fake()->boolean(),
            'is_confirmed' => fake()->boolean(),
            'requires_approval' => fake()->boolean(),

            'contact_id' => Contact::factory(),
            'order_type_id' => OrderType::factory(),
            'client_id' => Client::factory(),
            'currency_id' => Currency::factory(),
            'price_list_id' => PriceList::factory(),
        ];
    }
}
