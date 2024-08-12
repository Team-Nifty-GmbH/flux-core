<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\CustomEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @deprecated
 */
class CustomEventFactory extends Factory
{
    protected $model = CustomEvent::class;

    public function definition(): array
    {
        $i = 0;
        while (CustomEvent::query()
            ->where('name', $name = str_replace(' ', '', $this->faker->unique()->jobTitle()))
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $name .= Str::random(32);
        }

        return [
            'name' => $name,
        ];
    }
}
