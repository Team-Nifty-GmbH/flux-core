<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $i = 0;
        while (User::query()
            ->where('user_code', $userCode = Str::upper($this->faker->firstName()))
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $userCode .= '_' . Str::uuid();
        }

        return [
            'email' => $this->faker->safeEmail(),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'password' => 'password',
            'user_code' => $userCode,
            'is_active' => $this->faker->boolean(75),
        ];
    }
}
