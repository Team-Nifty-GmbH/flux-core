<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Widget;
use Illuminate\Database\Eloquent\Factories\Factory;

class WidgetFactory extends Factory
{
    protected $model = Widget::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle,
            'component_name' => $this->faker->randomElement(\FluxErp\Facades\Widget::all())['name'] ?? 'widgets.generic',
            'height' => $this->faker->numberBetween(1, 6),
            'width' => $this->faker->numberBetween(1, 12),
        ];
    }
}
