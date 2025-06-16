<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\RecordOrigin;
use Illuminate\Database\Seeder;

class RecordOriginTableSeeder extends Seeder
{
    public function run(): void
    {
        RecordOrigin::factory()->count(10)->create();
    }
}
