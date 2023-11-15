<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ContactBankConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactBankConnectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContactBankConnection::class;

    /**
     * Define the model's default state.
     */
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
