<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddressTableSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $contacts = Contact::all(['id']);

        foreach ($contacts as $contact) {
            Address::factory()->create([
                'contact_id' => $contact->id,
                'password' => $password,
                'is_main_address' => true,
            ]);
        }
    }
}
