<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Unit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Kilograms', 'Centimeters', 'Litres']),
            'abbreviation' => $this->faker->randomElement(['kg', 'cm', 'l']),
        ];
    }
}
