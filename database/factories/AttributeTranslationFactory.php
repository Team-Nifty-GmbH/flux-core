<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\AttributeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeTranslationFactory extends Factory
{
    protected $model = AttributeTranslation::class;

    public function definition(): array
    {
        return [
            'attribute' => $this->faker->text(20),
            'value' => $this->faker->text(60),
        ];
    }
}
