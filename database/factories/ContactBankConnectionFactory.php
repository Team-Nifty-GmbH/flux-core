<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ContactBankConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactBankConnectionFactory extends Factory
{
    protected $model = ContactBankConnection::class;

    public function definition(): array
    {
        return [
            'iban' => fake()->iban(),
            'account_holder' => fake()->name,
            'bank_name' => fake()->name,
            'bic' => fake()->bothify('##????##?#?'),
        ];
    }
}
