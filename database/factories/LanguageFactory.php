<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $i = 0;
        while (Language::query()
            ->where('language_code', $languageCode = $this->faker->unique()->languageCode())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $languageCode .= '_' . Str::uuid();
        }

        return [
            'name' => $this->faker->country(),
            'iso_name' => $this->faker->country(),
            'language_code' => $languageCode,
        ];
    }
}
