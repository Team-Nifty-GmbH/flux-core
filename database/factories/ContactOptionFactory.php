<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ContactOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactOptionFactory extends Factory
{
    protected $model = ContactOption::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['phone', 'email', 'website']);

        return [
            'type' => $type,
            'label' => fake()->jobTitle(),
            'value' => match ($type) {
                'email' => fake()->email(),
                'phone' => fake()->phoneNumber(),
                'website' => fake()->url(),
            },
        ];
    }
}
