<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Industry;
use Illuminate\Database\Seeder;

class IndustryTableSeeder extends Seeder
{
    public function run(): void
    {
        Industry::factory()->count(50)->create();
    }
}
