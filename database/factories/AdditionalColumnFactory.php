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
            'label' => $this->faker->text(20),
            'is_translatable' => $this->faker->boolean(),
            'is_customer_editable' => $this->faker->boolean(),
            'is_frontend_visible' => $this->faker->boolean(),
        ];
    }
}
