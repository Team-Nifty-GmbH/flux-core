<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\BankConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankConnectionFactory extends Factory
{
    protected $model = BankConnection::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'account_holder' => $this->faker->name,
            'bank_name' => $this->faker->name,
            'iban' => $this->faker->bothify('??####################'),
            'bic' => $this->faker->bothify('##????##?#?'),
        ];
    }
}
