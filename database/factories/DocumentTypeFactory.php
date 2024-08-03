<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @deprecated
 */
class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    public function definition(): array
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
