<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\PriceList;
use Illuminate\Database\Seeder;

class PriceListTableSeeder extends Seeder
{
    public function run(): void
    {
        PriceList::factory()->count(10)->create();
    }
}
