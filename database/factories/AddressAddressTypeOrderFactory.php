<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressAddressTypeOrderFactory extends Factory
{
    protected $model = AddressAddressTypeOrder::class;

    public function definition(): array
    {
        return [
            'address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'zip' => $this->faker->postcode(),
                'country' => $this->faker->country(),
                'name' => $this->faker->name(),
                'company' => $this->faker->company(),
            ],
        ];
    }
}
