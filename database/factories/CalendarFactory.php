<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarFactory extends Factory
{
    protected $model = Calendar::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->jobTitle(),
            'color' => $this->faker->hexColor(),
            'is_public' => $this->faker->boolean(),
        ];
    }
}
