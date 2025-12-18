<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\RecordOrigin;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecordOriginFactory extends Factory
{
    protected $model = RecordOrigin::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'is_active' => fake()->boolean(85),
        ];
    }
}
