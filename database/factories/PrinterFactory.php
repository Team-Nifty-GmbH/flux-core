<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Printer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PrinterFactory extends Factory
{
    protected $model = Printer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Printer',
            'spooler_name' => Str::slug($this->faker->word . ' printer'),
            'location' => $this->faker->boolean() ? $this->faker->city : null,
            'make_and_model' => $this->faker->boolean()
                ? $this->faker->company . ' ' . $this->faker->bothify('Model-##?') : null,
            'media_sizes' => $this->faker->randomElements(
                ['A2', 'A3', 'A4', 'A5', 'A6'],
                $this->faker->numberBetween(2, 3)
            ),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
