<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'retention_years' => fake()->numberBetween(1, 10),
            'is_owner_uploadable' => false,
            'is_active' => true,
        ];
    }
}
