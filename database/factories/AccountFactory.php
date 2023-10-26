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
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'account_number' => $this->faker->numerify('##########'),
            'account_holder' => $this->faker->name(),
            'iban' => $this->faker->iban(),
            'type' => $this->faker->randomElement(['bank', 'cash']),
        ];
    }
}
