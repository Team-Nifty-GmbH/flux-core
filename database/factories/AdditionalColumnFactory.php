<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\AdditionalColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdditionalColumnFactory extends Factory
{
    protected $model = AdditionalColumn::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
        ];
    }
}
