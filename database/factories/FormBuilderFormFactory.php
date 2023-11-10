<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\FormBuilderForm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FormBuilderFormFactory extends Factory
{
    protected $model = FormBuilderForm::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'slug' => $this->faker->slug(),
            'start_date' => Carbon::now()->subWeeks(rand(1, 52)),
            'end_date' => Carbon::now()->addWeeks(rand(1, 52)),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
