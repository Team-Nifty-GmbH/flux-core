<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressTypeFactory extends Factory
{
    protected $model = AddressType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'is_locked' => fake()->boolean(),
            'is_unique' => fake()->boolean(),
        ];
    }
}
