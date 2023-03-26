<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DocumentType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'description' => $this->faker->sentence(),
            'additional_header' => $this->faker->sentence(),
            'additional_footer' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
