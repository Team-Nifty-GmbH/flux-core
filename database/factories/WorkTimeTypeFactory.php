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
            'name' => $this->faker->word,
            'is_billable' => $this->faker->boolean(),
        ];
    }
}
