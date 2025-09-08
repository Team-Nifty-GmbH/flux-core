<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'value_date' => fake()->date(),
            'booking_date' => fake()->date(),
            'amount' => fake()->randomFloat(),
            'purpose' => fake()->text(),
            'type' => fake()->jobTitle(),
            'counterpart_name' => fake()->name(),
            'counterpart_iban' => fake()->iban(),
            'counterpart_bank_name' => fake()->company(),
            'bank_connection_id' => \FluxErp\Models\BankConnection::factory(),
            'currency_id' => \FluxErp\Models\Currency::factory(),
        ];
    }
}
