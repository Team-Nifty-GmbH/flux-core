<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'resource_number' => fake()->unique()->bothify('RES-####'),
            'allow_overbooking' => false,
            'is_active' => true,
        ];
    }
}
