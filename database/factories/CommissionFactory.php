<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Commission;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Commission::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'commission' => $this->faker->randomFloat(5, 0.01),
        ];
    }
}
