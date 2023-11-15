<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use Illuminate\Database\Seeder;

class ContactBankConnectionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = Contact::all();
        foreach ($contacts as $contact) {
            ContactBankConnection::factory()->count(rand(0, 3))->create([
                'contact_id' => $contact->id,
            ]);
        }
    }
}
