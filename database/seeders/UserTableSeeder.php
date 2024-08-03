<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Facades\Widget;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    public function run()
    {
        $languages = Language::all('id');
        $password = Hash::make('password');

        User::factory(10)
            ->create([
                'password' => $password,
                'language_id' => fn () => $languages->random()->id,
            ])
            ->each(function (User $user) {
                $user->assignRole('Super Admin');
                foreach(Widget::all() as $widget) {
                    $user->widgets()->create([
                        'name' => $widget['label'],
                        'component_name' => $widget['component_name'],
                        'width' => rand(3, 6),
                    ]);
                }
            });
    }
}
