<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        TicketType::factory()->count(10)->create();
    }
}
