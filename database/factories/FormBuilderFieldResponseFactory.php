<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\FormBuilderFieldResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormBuilderFieldResponseFactory extends Factory
{
    protected $model = FormBuilderFieldResponse::class;

    public function definition(): array
    {
        return [
            'response' => $this->faker->word(),
        ];
    }
}
