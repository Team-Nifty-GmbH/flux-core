<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\BankConnection;
use FluxErp\Models\LanguageLine;
use Illuminate\Database\Seeder;

class LanguageLineTableSeeder extends Seeder
{
    public function run(): void
    {
        LanguageLine::factory()->count(rand(10, 30))->create();
    }
}
