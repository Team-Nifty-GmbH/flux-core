<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use Illuminate\Database\Seeder;

class ContactTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all(['id']);

        foreach ($clients as $client) {
            Contact::factory()->count(10)->create([
                'client_id' => $client->id,
            ]);
        }
    }
}
