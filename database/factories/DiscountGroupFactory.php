<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountGroupFactory extends Factory
{
    protected $model = DiscountGroup::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid,
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
