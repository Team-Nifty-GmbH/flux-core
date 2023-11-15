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
            'iban' => $this->faker->bothify('??####################'),
            'account_holder' => $this->faker->name,
            'bank_name' => $this->faker->name,
            'bic' => $this->faker->bothify('##????##?#?'),
        ];
    }
}
