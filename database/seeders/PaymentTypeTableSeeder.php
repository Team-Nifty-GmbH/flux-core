<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all(['id']);

        foreach ($clients as $client) {
            PaymentType::factory()->count(2)->create([
                'client_id' => $client->id,
            ]);
        }
    }
}
