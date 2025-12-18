<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkTimeTypeFactory extends Factory
{
    protected $model = WorkTimeType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'is_billable' => fake()->boolean(),
        ];
    }
}
