<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\AdditionalColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdditionalColumnFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdditionalColumn::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
        ];
    }
}
