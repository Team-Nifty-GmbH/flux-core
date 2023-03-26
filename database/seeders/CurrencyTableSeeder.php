<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    public function run()
    {
        Currency::factory()->count(5)->create();
    }
}
