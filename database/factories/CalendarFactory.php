<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FluxErp\Models\Calendar>
 */
class CalendarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Calendar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->jobTitle(),
            'color' => $this->faker->hexColor(),
            'is_public' => $this->faker->boolean(),
        ];
    }
}
