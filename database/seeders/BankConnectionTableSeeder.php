<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use Illuminate\Database\Seeder;

class BankConnectionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = Contact::all();
        foreach ($contacts as $contact) {
            BankConnection::factory()->count(rand(0, 3))->create([
                'contact_id' => $contact->id,
            ]);
        }

        // bank connection with no contact
        BankConnection::factory()->count(3)->create();
    }
}
