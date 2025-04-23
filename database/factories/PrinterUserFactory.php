<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Pivots\PrinterUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrinterUserFactory extends Factory
{
    protected $model = PrinterUser::class;

    public function definition(): array
    {
        return [
            'default_size' => $this->faker->boolean() ?
                $this->faker->randomElement(['A2', 'A3', 'A4', 'A5', 'A6']) : null,
            'is_default' => $this->faker->boolean(25),
        ];
    }
}
