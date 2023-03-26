<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Language;
use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    public function run()
    {
        Language::factory()->count(5)->create();
    }
}
