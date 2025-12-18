<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        $i = 0;
        while (Currency::query()
            ->where('iso', $iso = fake()->unique()->currencyCode())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $iso .= '_' . Str::uuid();
        }

        return [
            'name' => fake()->currencyCode(),
            'iso' => $iso,
            'symbol' => fake()->randomElement(['?', '€', '$', '#', '§']),
            'is_default' => false,
        ];
    }
}
