<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'bank_connection_id' => $this->faker->randomDigitNotNull,
            'currency_id' => $this->faker->randomDigitNotNull,
            'name' => $this->faker->company,
            'account_number' => $this->faker->bankAccountNumber,
            'account_holder' => $this->faker->name,
            'iban' => $this->faker->iban('DE'),
            'type' => $this->faker->word,
        ];
    }
}
