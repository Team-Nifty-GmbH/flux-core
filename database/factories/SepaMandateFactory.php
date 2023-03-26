<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\SepaMandate;
use Illuminate\Database\Eloquent\Factories\Factory;

class SepaMandateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SepaMandate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'signed_date' => $this->faker->boolean ? $this->faker->date : null,
        ];
    }
}
