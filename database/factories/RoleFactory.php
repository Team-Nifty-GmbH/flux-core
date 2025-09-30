<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }
}
