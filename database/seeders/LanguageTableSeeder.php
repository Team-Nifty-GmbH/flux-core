<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Language;
use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    public function run(): void
    {
        if (Language::query()->where('language_code', 'en')->doesntExist()) {
            Language::factory()->create([
                'name' => 'English',
                'language_code' => 'en',
                'is_default' => true,
            ]);
        }

        if (Language::query()->where('language_code', 'de')->doesntExist()) {
            Language::factory()->create([
                'name' => 'Deutsch',
                'language_code' => 'de',
            ]);
        }

        Language::factory()->count(5)->create();
    }
}
