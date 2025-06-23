<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\LanguageLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageLineFactory extends Factory
{
    protected $model = LanguageLine::class;

    public function definition(): array
    {
        return [
            'group' => $this->faker->word(),
            'key' => $this->faker->slug(),
            'text' => [
                'en' => $this->faker->sentence(),
                'de' => $this->faker->sentence(),
            ],
        ];
    }
}
