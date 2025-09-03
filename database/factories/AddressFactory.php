<?php

namespace FluxErp\Database\Factories;

use FluxErp\Enums\SalutationEnum;
use FluxErp\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'company' => fake()->boolean ? fake()->company() : null,
            'title' => fake()->boolean ? fake()->randomElement(['Dr.', 'Pr.', 'Dipl.']) : null,
            'salutation' => fake()->boolean ? fake()->randomElement(SalutationEnum::values()) : null,
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'addition' => fake()->boolean ? fake()->realText(maxNbChars: 20) : null,
            'mailbox' => fake()->boolean ? fake()->realText(maxNbChars: 20) : null,
            'latitude' => fake()->latitude,
            'longitude' => fake()->longitude,
            'zip' => fake()->postcode(),
            'city' => fake()->city(),
            'street' => fake()->streetAddress,
            'url' => fake()->boolean ? fake()->url : null,
            'email_primary' => uniqid() . fake()->safeEmail(),
            'date_of_birth' => fake()->boolean ? fake()->date : null,
            'department' => fake()->boolean ? fake()->realText(maxNbChars: 20) : null,
            'email' => uniqid() . fake()->unique()->safeEmail(),
            'password' => 'password',
            'is_active' => fake()->boolean(90),
            'is_main_address' => fake()->boolean,
            'can_login' => fake()->boolean(80),
        ];
    }
}
