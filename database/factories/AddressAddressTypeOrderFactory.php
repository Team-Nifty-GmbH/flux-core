<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressAdressTypeOrderFaktory extends Factory
{
    protected $model = AddressType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'is_locked' => $this->faker->boolean(),
            'is_unique' => $this->faker->boolean(),
        ];
    }
}
