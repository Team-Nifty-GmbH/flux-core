<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $i = 0;
        while (Language::query()
            ->where('language_code', $languageCode = fake()->unique()->languageCode())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $languageCode .= '_' . Str::uuid();
        }

        return [
            'name' => fake()->country(),
            'iso_name' => fake()->country(),
            'language_code' => $languageCode,
        ];
    }
}
