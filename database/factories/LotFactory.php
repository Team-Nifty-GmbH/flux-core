<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Lot;
use Illuminate\Database\Eloquent\Factories\Factory;

class LotFactory extends Factory
{
    protected $model = Lot::class;

    public function definition(): array
    {
        return [
            'lot_number' => fake()->unique()->bothify('LOT-#####'),
        ];
    }
}
