<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\CommissionRate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CommissionRate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'commission_rate' => $this->faker->randomFloat(min: 0.001, max: 0.9999),
        ];
    }
}
