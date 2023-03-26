<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'payment_target_days' => $this->faker->boolean ? $this->faker->numberBetween(int1: 1, int2: 30) : null,
            'payment_reminder_days_1' => $this->faker->numberBetween(int1: 1, int2: 30),
            'payment_reminder_days_2' => $this->faker->numberBetween(int1: 1, int2: 30),
            'payment_reminder_days_3' => $this->faker->numberBetween(int1: 1, int2: 30),
            'discount_days' => $this->faker->boolean ? $this->faker->numberBetween(int1: 1, int2: 50) : null,
            'discount_percent' => $this->faker->boolean ? $this->faker->randomFloat(nbMaxDecimals: 2, max: 5) : null,
            'credit_line' => $this->faker->boolean ?
                $this->faker->randomFloat(nbMaxDecimals: 2, min: 100, max:10000) : null,
            'has_delivery_lock' => $this->faker->boolean,
            'has_sensitive_reminder' => $this->faker->boolean,
        ];
    }
}
