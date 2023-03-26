<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\SerialNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class SerialNumberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SerialNumber::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'serial_number' => $this->faker->uuid(),
        ];
    }
}
