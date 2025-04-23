<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleTableSeeder extends Seeder
{
    public function run(): void
    {
        Schedule::factory()->count(10)->create();
    }
}
