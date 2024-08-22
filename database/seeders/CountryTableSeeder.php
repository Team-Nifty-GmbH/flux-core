<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Country;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use Illuminate\Database\Seeder;

class CountryTableSeeder extends Seeder
{
    public function run(): void
    {
        $languages = Language::all(['id']);
        $currencies = Currency::all(['id']);

        if ($languages and $currencies) {
            for ($i = 0; $i < 5; $i++) {
                Country::factory()->create([
                    'language_id' => $languages->random()->id,
                    'currency_id' => $currencies->random()->id,
                ]);
            }
        }
    }
}
