<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    public function run()
    {
        $languages = Language::all();

        if ($languages) {
            for ($i = 0; $i < 10; $i++) {
                User::factory()->create([
                    'language_id' => $languages->random()->id,
                ])->each(function ($user) {
                    $user->assignRole('Super Admin');
                });
            }
        }
    }
}
