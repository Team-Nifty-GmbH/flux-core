<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Unit;
use Illuminate\Database\Seeder;

class UnitTableSeeder extends Seeder
{
    public function run(): void
    {
        Unit::factory()->count(5)->create();
    }
}
