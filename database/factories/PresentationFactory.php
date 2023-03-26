<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Presentation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PresentationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Presentation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'notice' => $this->faker->text(),
        ];
    }
}
