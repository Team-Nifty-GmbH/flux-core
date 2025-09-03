<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Kilograms', 'Centimeters', 'Litres']),
            'abbreviation' => fake()->randomElement(['kg', 'cm', 'l']),
        ];
    }
}
