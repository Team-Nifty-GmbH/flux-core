<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'company' => $this->faker->boolean ? $this->faker->company() : null,
            'title' => $this->faker->boolean ? $this->faker->randomElement(['Dr.', 'Pr.', 'Dipl.']) : null,
            'salutation' => $this->faker->boolean ? $this->faker->randomElement(['Herr', 'Frau', 'Firma']) : null,
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'addition' => $this->faker->boolean ? $this->faker->realText(maxNbChars: 20) : null,
            'mailbox' => $this->faker->boolean ? $this->faker->realText(maxNbChars: 20) : null,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'zip' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'street' => $this->faker->streetAddress,
            'url' => $this->faker->boolean ? $this->faker->url : null,
            'date_of_birth' => $this->faker->boolean ? $this->faker->date : null,
            'department' => $this->faker->boolean ? $this->faker->realText(maxNbChars: 20) : null,
            'email' => uniqid() . $this->faker->unique()->safeEmail(),
            'password' => 'password',
            'is_active' => $this->faker->boolean(90),
            'is_main_address' => $this->faker->boolean,
            'can_login' => $this->faker->boolean(80),
        ];
    }
}
