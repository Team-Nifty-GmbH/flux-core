<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\SerialNumberRange;
use Illuminate\Database\Eloquent\Factories\Factory;

class SerialNumberRangeFactory extends Factory
{
    protected $model = SerialNumberRange::class;

    public function definition(): array
    {
        return [
            'current_number' => rand(min: 1, max: 10000),
            'prefix' => fake()->countryISOAlpha3(),
            'suffix' => fake()->countryISOAlpha3(),
            'description' => fake()->text(),
        ];
    }
}
