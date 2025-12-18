<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Industry;
use Illuminate\Database\Eloquent\Factories\Factory;

class IndustryFactory extends Factory
{
    protected $model = Industry::class;

    public function definition(): array
    {
        return [
            'name' => fake()->jobTitle(),
        ];
    }
}
