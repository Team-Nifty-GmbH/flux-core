<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class ClientUserTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(bcfloor($clientIds->count() * 0.8));

        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.8));

        foreach ($cutClientIds as $categoryId) {
            $categoryId->users()->attach($cutUserIds->random(
                rand(1, max(1, bcfloor($cutUserIds->count() * 0.4)))
            ));
        }
    }
}
