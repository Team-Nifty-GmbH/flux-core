<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'group' => fake()->word(),
            'name' => fake()->word(),
            'locked' => fake()->boolean(10),
            'payload' => json_encode(fake()->words(3)),
        ];
    }
}
