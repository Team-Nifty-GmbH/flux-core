<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    public function run(): void
    {
        if (Currency::query()->where('iso', 'EUR')->doesntExist()) {
            Currency::factory()->create([
                'name' => 'Euro',
                'iso' => 'EUR',
                'symbol' => '€',
                'is_default' => true,
            ]);
        }

        Currency::factory()->count(3)->create();
    }
}
