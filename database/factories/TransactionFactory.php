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
            'value_date' => $this->faker->date(),
            'booking_date' => $this->faker->date(),
            'amount' => $this->faker->randomFloat(),
            'purpose' => $this->faker->text(),
            'type' => $this->faker->jobTitle(),
            'counterpart_name' => $this->faker->name(),
            'counterpart_iban' => $this->faker->iban(),
            'counterpart_bank_name' => $this->faker->company(),
            'bank_connection_id' => \FluxErp\Models\BankConnection::factory(),
            'currency_id' => \FluxErp\Models\Currency::factory(),
        ];
    }
}
