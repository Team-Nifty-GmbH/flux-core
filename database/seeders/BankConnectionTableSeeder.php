<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\BankConnection;
use Illuminate\Database\Seeder;

class BankConnectionTableSeeder extends Seeder
{
    public function run(): void
    {
        BankConnection::factory()->count(rand(0, 3))->create();
    }
}
