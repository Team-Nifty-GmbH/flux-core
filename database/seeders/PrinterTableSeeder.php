<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Printer;
use Illuminate\Database\Seeder;

class PrinterTableSeeder extends Seeder
{
    public function run(): void
    {
        Printer::factory()->count(5)->create();
    }
}
