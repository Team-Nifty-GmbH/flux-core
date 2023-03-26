<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

class VatRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VatRate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Standard', 'Special', 'Reduced']),
            'rate_percentage' => $this->faker->randomElement([0.19, 0.16, 0.07]),
        ];
    }
}
