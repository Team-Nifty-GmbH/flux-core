<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => $this->faker->safeEmail(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'password' => 'password',
            'user_code' => $this->faker->unique()->userName(),
            'is_active' => $this->faker->boolean(75),
        ];
    }
}
