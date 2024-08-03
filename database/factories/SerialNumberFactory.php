<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\SerialNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class SerialNumberFactory extends Factory
{
    protected $model = SerialNumber::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'serial_number' => $this->faker->uuid(),
        ];
    }
}
