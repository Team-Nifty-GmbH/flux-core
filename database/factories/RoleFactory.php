<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
