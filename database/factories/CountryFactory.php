<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CountryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Country::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $i = 0;
        while (Country::query()
            ->where('iso_alpha2', $isoAlpha2 = $this->faker->unique()->countryCode())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $isoAlpha2 .= '_' . Str::uuid();
        }

        return [
            'name' => $this->faker->country(),
            'iso_alpha2' => $isoAlpha2,
            'iso_alpha3' => $this->faker->countryISOAlpha3(),
            'iso_numeric' => $this->faker->numerify('###'),
            'is_active' => $this->faker->boolean(90),
            'is_default' => false,
            'is_eu_country' => $this->faker->boolean(66),
        ];
    }
}
