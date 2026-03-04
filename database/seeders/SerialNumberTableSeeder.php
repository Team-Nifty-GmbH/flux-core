<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\SerialNumber;
use Illuminate\Database\Seeder;

class SerialNumberTableSeeder extends Seeder
{
    public function run(): void
    {
        SerialNumber::factory()->count(10)->create();
    }
}
