<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\SerialNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

class SerialNumberFactory extends Factory
{
    protected $model = SerialNumber::class;

    public function definition(): array
    {
        return [
            'serial_number' => $this->faker->uuid(),
        ];
    }
}
