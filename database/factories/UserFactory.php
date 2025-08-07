<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->safeEmail(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'name' => $this->faker->name(),
            'password' => 'password',
            'user_code' => $this->faker->unique()->userName(),
            'is_active' => $this->faker->boolean(75),
            'language_id' => 1,
        ];
    }
}
