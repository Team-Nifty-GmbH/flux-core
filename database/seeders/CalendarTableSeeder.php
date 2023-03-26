<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Calendar;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class CalendarTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Calendar::factory()->count(5)->create([
            'is_public' => true,
        ]);

        foreach (User::all() as $user) {
            Calendar::factory()->count(3)->create([
                'user_id' => $user->id,
                'is_public' => false,
            ]);
        }
    }
}
