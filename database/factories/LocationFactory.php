<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'street' => $this->faker->streetName(),
            'house_number' => $this->faker->buildingNumber(),
            'zip' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'country_id' => null,
            'country_region_id' => null,
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'is_active' => true,
            'client_id' => 1,
        ];
    }
}