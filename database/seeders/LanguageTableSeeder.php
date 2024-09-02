<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Language;
use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    public function run(): void
    {
        Language::factory()->create([
            'name' => 'English',
            'language_code' => 'en',
            'is_default' => true,
        ]);
        Language::factory()->create([
            'name' => 'Deutsch',
            'language_code' => 'de',
        ]);

        Language::factory()->count(5)->create();
    }
}
