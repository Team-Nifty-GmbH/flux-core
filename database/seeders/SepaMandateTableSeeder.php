<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use Illuminate\Database\Seeder;

class SepaMandateTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $contactBankConnections = ContactBankConnection::all();
        foreach ($contactBankConnections as $contactBankConnection) {
            SepaMandate::factory()->count(rand(0, 3))->create([
                'contact_bank_connection_id' => $contactBankConnection->id,
                'contact_id' => $contactBankConnection->contact_id,
                'client_id' => $clients->random()->first()->id,
            ]);
        }
    }
}
