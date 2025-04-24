<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Client;
use Illuminate\Database\Seeder;

class BankConnectionClientTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(bcfloor($clientIds->count() * 0.75));

        $bankConnectionIds = BankConnection::query()->get('id');
        $cutBankConnectionIds = $bankConnectionIds->random(bcfloor($bankConnectionIds->count() * 0.75));

        foreach ($cutClientIds as $clientId) {
            $clientId->bankConnections()->attach($cutBankConnectionIds->random(
                rand(1, max(1, bcfloor($cutBankConnectionIds->count() * 0.75)))
            ));
        }
    }
}
