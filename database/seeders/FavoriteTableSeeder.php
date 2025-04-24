<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Favorite;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class FavoriteTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $idList = User::query()->pluck('id')->toArray();
            $instanceId = faker()->unique()->randomElement($idList);

            Favorite::firstOrCreate(Favorite::factory()->make([
                'authenticatable_type' => User::class,
                'authenticatable_id' => $instanceId,
            ])->toArray());
        }
    }
}
