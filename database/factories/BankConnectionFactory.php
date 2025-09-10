<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\BankConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankConnectionFactory extends Factory
{
    protected $model = BankConnection::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'account_holder' => fake()->name,
            'bank_name' => fake()->name,
            'iban' => fake()->bothify('??####################'),
            'bic' => fake()->bothify('##????##?#?'),
        ];
    }
}
