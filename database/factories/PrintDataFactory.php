<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\PrintData;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrintDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PrintData::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'data' => null,
            'view' => null,
            'template_name' => null,
            'is_public' => $this->faker->boolean(),
            'is_template' => $this->faker->boolean(),
        ];
    }
}
