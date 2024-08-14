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
            ->where('language_code', $languageCode = $this->faker->unique()->languageCode())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $languageCode .= '_'.Str::uuid();
        }

        return [
            'name' => $this->faker->country(),
            'iso_name' => $this->faker->country(),
            'language_code' => $languageCode,
        ];
    }
}
