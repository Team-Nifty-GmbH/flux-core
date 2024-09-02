<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Facades\Widget;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    public function run(): void
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
                $row = 0;
                $col = 0;
                foreach (Arr::sort(Widget::all(), 'defaultWidth') as $widget) {
                    $user->widgets()->create([
                        'name' => $widget['label'],
                        'component_name' => $widget['component_name'],
                        'width' => data_get($widget, 'defaultWidth', 1),
                        'height' => data_get($widget, 'defaultHeight', 1),
                        'order_column' => $col,
                        'order_row' => $row,
                    ]);

                    $col += data_get($widget, 'defaultWidth', 1);
                    if ($col >= 6) {
                        $col = 0;
                        $row++;
                    }
                }
            });
    }
}
