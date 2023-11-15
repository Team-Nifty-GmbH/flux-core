<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\FormBuilderSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormBuilderSectionFactory extends Factory
{
    protected $model = FormBuilderSection::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'columns' => $this->faker->numberBetween(1, 12),
        ];
    }
}
