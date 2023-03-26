<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductOptionGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['color', 'material', 'size', 'gender']),
        ];
    }
}
