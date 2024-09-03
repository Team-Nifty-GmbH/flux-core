<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\ContactOrigin;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactOriginFactory extends Factory
{
    protected $model = ContactOrigin::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean(85),
        ];
    }
}
