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
        return [
            'type' => $this->faker->randomElement(['phone', 'email', 'website']),
            'label' => $this->faker->jobTitle(),
            'value' => $this->faker->email(),
        ];
    }
}
