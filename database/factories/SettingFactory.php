<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $i = 0;
        while (Setting::query()
            ->where('key', $key = $this->faker->jobTitle())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $key .= '_' . Str::uuid();
        }

        return [
            'key' => $key,
            'settings' => $this->faker->randomElements(),
        ];
    }
}
