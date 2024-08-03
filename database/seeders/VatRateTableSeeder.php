<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\VatRate;
use Illuminate\Database\Seeder;

class VatRateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VatRate::factory()->count(3)->create();
    }
}
