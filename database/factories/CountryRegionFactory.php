<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\CountryRegion;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryRegionFactory extends Factory
{
    protected $model = CountryRegion::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
