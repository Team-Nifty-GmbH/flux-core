<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Widget;
use Illuminate\Database\Eloquent\Factories\Factory;

class WidgetFactory extends Factory
{
    protected $model = Widget::class;

    public function definition(): array
    {
        $widget = $this->faker->randomElement(\FluxErp\Facades\Widget::all());

        return [
            'name' => $this->faker->jobTitle,
            'component_name' => $widget['name'] ?? 'widgets.generic',
            'height' => method_exists($widget, 'getDefaultHeight')
                ? $widget['class']::getDefaultHeight()
                : $this->faker->numberBetween(1, 12),
            'width' => method_exists($widget, 'getDefaultWidth')
                ? $widget['class']::getDefaultWidth()
                : $this->faker->numberBetween(1, 12),
        ];
    }
}
