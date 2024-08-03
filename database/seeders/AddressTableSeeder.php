<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddressTableSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        $password = Hash::make('password');

        foreach ($clients as $client) {
            $contacts = Contact::query()
                ->where('client_id', $client->id)
                ->get(['id']);

            foreach ($contacts as $contact) {
                Address::factory()->create([
                    'client_id' => $client->id,
                    'contact_id' => $contact->id,
                    'password' => $password,
                    'is_main_address' => true,
                ]);
            }
        }
    }
}
