<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\LanguageLine;
use Illuminate\Database\Seeder;

class LanguageLineTableSeeder extends Seeder
{
    public function run(): void
    {
        $langs = [
            'en' => base_path('packages/flux-core/lang/en.json'),
            'de' => base_path('packages/flux-core/lang/de.json'),
        ];

        $randomKeys = [];
        $translations = [];

        if (file_exists(data_get($langs, 'en'))) { // smallest common base
            $jsonString = file_get_contents(data_get($langs, 'en'));
            $data = json_decode($jsonString, true);

            $randomKeys = array_rand($data, 30);
        }

        foreach ($langs as $langKey => $langPath) {
            $data = json_decode(file_get_contents($langPath), true);
            foreach ($randomKeys as $key) {
                $translations[$key][$langKey] = $data[$key];
            }
        }

        foreach ($translations as $key => $texts) {
            $group = explode('.', $key)[0];

            LanguageLine::updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['text' => $texts]
            );
        }
    }
}
