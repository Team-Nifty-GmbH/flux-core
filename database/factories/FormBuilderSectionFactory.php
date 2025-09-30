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
            'name' => fake()->name(),
            'description' => fake()->text(),
            'ordering' => fake()->randomNumber(),
            'columns' => fake()->randomNumber(),
        ];
    }
}
