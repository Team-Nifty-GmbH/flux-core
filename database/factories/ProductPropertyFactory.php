<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ProductProperty;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPropertyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductProperty::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['dimension_height', 'dimension_width', 'dimension_length', 'weight_grams']),
        ];
    }
}
