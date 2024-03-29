<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PriceList;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceListFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PriceList::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->jobTitle(),
            'price_list_code' => $this->faker->unique()->countryCode(),
            'is_net' => $this->faker->boolean(),
        ];
    }
}
