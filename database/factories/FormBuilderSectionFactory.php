<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FormBuilderSectionFactory extends Factory
{
    protected $model = FormBuilderSection::class;

    public function definition(): array
    {
        $form = FormBuilderForm::all();

        return [
            'form_id' => $form->random(),
            'name' => $this->faker->name(),
            'ordering' => $this->faker->randomNumber(),
            'columns' => $this->faker->randomNumber(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ];
    }
}
