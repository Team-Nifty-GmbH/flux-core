<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $i = 0;
        while (Currency::query()
                ->where('iso', $iso = $this->faker->unique()->currencyCode())
                ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $iso .= '_' . Str::uuid();
        }

        return [
            'name' => $this->faker->currencyCode(),
            'iso' => $iso,
            'symbol' => $this->faker->randomElement(['?', '€', '$', '#', '§']),
            'is_default' => false,
        ];
    }
}
