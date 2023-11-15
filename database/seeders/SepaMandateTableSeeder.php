<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use Illuminate\Database\Seeder;

class SepaMandateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $bankConnections = ContactBankConnection::all();
        foreach ($bankConnections as $bankConnection) {
            SepaMandate::factory()->count(rand(0, 3))->create([
                'bank_connection_id' => $bankConnection->id,
                'contact_id' => $bankConnection->contact_id,
                'client_id' => $clients->random()->first()->id,
            ]);
        }
    }
}
