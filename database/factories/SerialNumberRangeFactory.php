<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\SerialNumberRange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class SerialNumberRangeFactory extends Factory
{
    protected $model = SerialNumberRange::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'current_number' => rand(min: 1, max: 10000),
            'prefix' => $this->faker->countryISOAlpha3(),
            'suffix' => $this->faker->countryISOAlpha3(),
            'description' => $this->faker->text(),
        ];
    }
}
