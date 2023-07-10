<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value_date' => $this->faker->date(),
            'booking_date' => $this->faker->date(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'purpose' => $this->faker->sentence,
            'type' => $this->faker->word,
            'counterpart_name' => $this->faker->name,
            'counterpart_account_number' => $this->faker->bankAccountNumber,
            'counterpart_iban' => $this->faker->iban('DE'),
            'counterpart_bic' => $this->faker->swiftBicNumber,
            'counterpart_bank_name' => $this->faker->company,
        ];
    }
}
