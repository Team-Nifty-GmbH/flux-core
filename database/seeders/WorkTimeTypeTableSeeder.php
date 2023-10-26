<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Seeder;

class WorkTimeTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        WorkTimeType::factory()->count(5)->create();
    }
}
