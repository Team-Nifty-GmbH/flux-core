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
            'email' => fake()->safeEmail(),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'name' => fake()->name(),
            'password' => 'password',
            'user_code' => fake()->unique()->userName(),
            'is_active' => fake()->boolean(75),
        ];
    }
}
