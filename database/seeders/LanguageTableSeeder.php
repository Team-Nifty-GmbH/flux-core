<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Language;
use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    public function run(): void
    {
        Language::factory()->count(5)->create();
    }
}
