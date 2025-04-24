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
            'group' => $this->faker->text(20),
            'key' => $this->faker->text(10),
            'text' => [
                'en' => $this->faker->realText(60),
                'de' => $this->faker->realText(60),
            ],
        ];
    }
}
