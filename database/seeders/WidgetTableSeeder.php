<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\User;
use FluxErp\Models\Widget;
use Illuminate\Database\Seeder;

class WidgetTableSeeder extends Seeder
{
    public function run()
    {
        foreach (User::all() as $user) {
            $widgets = rand(1, 10);
            for ($i = 0; $i < $widgets; $i++) {
                Widget::factory()->create([
                    'widgetable_id' => $user->id,
                    'widgetable_type' => User::class,
                ]);
            }
        }
    }
}
