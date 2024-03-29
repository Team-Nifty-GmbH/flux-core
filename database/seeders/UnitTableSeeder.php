<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Unit;
use Illuminate\Database\Seeder;

class UnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Unit::factory()->count(5)->create();
    }
}
