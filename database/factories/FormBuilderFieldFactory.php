<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\FormBuilderField;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormBuilderFieldFactory extends Factory
{
    protected $model = FormBuilderField::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'ordering' => fake()->randomNumber(),
            'options' => fake()->words(),
        ];
    }
}
