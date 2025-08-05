<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\LeadState;
use Illuminate\Database\Seeder;

class LeadStateTableSeeder extends Seeder
{
    public function run(): void
    {
        LeadState::factory()
            ->count(5)
            ->create();

        LeadState::factory()
            ->create([
                'is_default' => true,
                'is_won' => false,
                'is_lost' => false,
            ]);
    }
}
