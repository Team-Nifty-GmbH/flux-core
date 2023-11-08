<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FormBuilderFieldResponseFactory extends Factory
{
    protected $model = FormBuilderFieldResponse::class;

    public function definition(): array
    {
        return [
            'response' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'form_id' => FormBuilderForm::factory(),
            'field_id' => FormBuilderField::factory(),
            'response_id' => FormBuilderResponse::factory(),
        ];
    }
}
