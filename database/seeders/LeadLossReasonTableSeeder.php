<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\LeadLossReason;
use Illuminate\Database\Seeder;

class LeadLossReasonTableSeeder extends Seeder
{
    public function run(): void
    {
        LeadLossReason::factory()->count(10)->create();
    }
}
