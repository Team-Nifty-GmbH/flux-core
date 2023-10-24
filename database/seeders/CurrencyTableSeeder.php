<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    public function run()
    {
        Currency::factory()->count(3)->create();

        if (! Currency::query()->where('is_default', true)->exists()) {
            Currency::factory()->create(['is_default' => true]);
        }
    }
}
