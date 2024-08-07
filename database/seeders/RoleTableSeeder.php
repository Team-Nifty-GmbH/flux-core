<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Role;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    public function run(): void
    {
        $guards = array_keys(config('auth.guards'));

        Role::factory()->count(5)->create(['guard_name' => function () use ($guards) {
            $key = array_rand($guards);

            return $guards[$key];
        }]);
    }
}
