<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PrintJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrintJobFactory extends Factory
{
    protected $model = PrintJob::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 10),
            'size' => $this->faker->randomElement(['A2', 'A3', 'A4', 'A5', 'A6']),
            'is_completed' => $this->faker->boolean(60),
        ];
    }
}
