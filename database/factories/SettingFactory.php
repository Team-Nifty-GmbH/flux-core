<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        $i = 0;
        while (Setting::query()
            ->where('key', $key = fake()->jobTitle())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $key .= '_' . Str::uuid();
        }

        return [
            'key' => $key,
            'settings' => fake()->randomElements(),
        ];
    }
}
