<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $employmentDate = $this->faker->date();
        $terminationDate = $this->faker->boolean(70)
            ? $this->faker->dateTimeBetween($employmentDate)->format('Y-m-d')
            : null;

        return [
            'email' => $this->faker->safeEmail(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'name' => $this->faker->name(),
            'password' => 'password',
            'timezone' => $this->faker->timezone(),
            'date_of_birth' => $this->faker->date(),
            'employment_date' => $employmentDate,
            'termination_date' => $terminationDate,
            'iban' => $this->faker->iban(),
            'bank_name' => $this->faker->company(),
            'bic' => $this->faker->bothify('##????##?#?'),
            'cost_per_hour' => $this->faker->numberBetween(13, 70),
            'is_dark_mode' => $this->faker->boolean(30),
            'user_code' => $this->faker->unique()->userName(),
            'is_active' => $this->faker->boolean(75),
        ];
    }
}
