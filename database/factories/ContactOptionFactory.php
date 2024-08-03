<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ContactOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactOptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContactOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['phone', 'email', 'website']);

        return [
            'type' => $type,
            'label' => $this->faker->jobTitle(),
            'value' => match ($type) {
                'email' => $this->faker->email(),
                'phone' => $this->faker->phoneNumber(),
                'website' => $this->faker->url(),
            },
        ];
    }
}
