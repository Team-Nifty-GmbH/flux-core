<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\LedgerAccount;
use Illuminate\Database\Seeder;

class LedgerAccountTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(bcfloor($clientIds->count() * 0.75));

        LedgerAccount::factory()->count(9)->create([
            'client_id' => fn () => $cutClientIds->random()->getKey(),
        ]);
    }
}
