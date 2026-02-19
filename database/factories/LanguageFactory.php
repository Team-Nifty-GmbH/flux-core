<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use NumberFormatter;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $i = 0;
        do {
            $languageCode = fake()->unique()->languageCode();
            $i++;
        } while (
            $i < 100
            && (
                Language::query()->where('language_code', $languageCode)->exists()
                || @(new NumberFormatter($languageCode, NumberFormatter::DECIMAL))->getErrorCode() !== 0
            )
        );

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
