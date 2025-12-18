<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->locale,
        ];
    }
}
