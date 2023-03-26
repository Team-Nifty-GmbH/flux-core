<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\CountryRegion;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryRegionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CountryRegion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
