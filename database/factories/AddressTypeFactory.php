<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AddressType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'is_locked' => $this->faker->boolean(),
            'is_unique' => $this->faker->boolean(),
        ];
    }
}
