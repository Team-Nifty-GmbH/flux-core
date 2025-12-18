<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Widget;
use Illuminate\Database\Eloquent\Factories\Factory;

class WidgetFactory extends Factory
{
    protected $model = Widget::class;

    public function definition(): array
    {
        $widget = fake()->randomElement(\FluxErp\Facades\Widget::all());

        return [
            'name' => fake()->jobTitle,
            'component_name' => $widget['name'] ?? 'widgets.generic',
            'height' => method_exists($widget, 'getDefaultHeight')
                ? $widget['class']::getDefaultHeight()
                : fake()->numberBetween(1, 12),
            'width' => method_exists($widget, 'getDefaultWidth')
                ? $widget['class']::getDefaultWidth()
                : fake()->numberBetween(1, 12),
        ];
    }
}
