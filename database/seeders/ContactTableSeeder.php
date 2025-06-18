<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\RecordOrigin;
use Illuminate\Database\Seeder;

class ContactTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all(['id']);
        $recordOrigins = RecordOrigin::query()->where('model_type', morph_alias(Contact::class))->get('id');

        foreach ($clients as $client) {
            Contact::factory()->count(10)->create([
                'client_id' => $client->id,
                'record_origin_id' => fn () => $recordOrigins->random()->id,
            ]);
        }
    }
}
