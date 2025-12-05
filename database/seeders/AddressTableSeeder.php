<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddressTableSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all(['id']);
        $password = Hash::make('password');

        foreach ($tenants as $tenant) {
            $contacts = Contact::query()
                ->where('tenant_id', $tenant->id)
                ->get(['id']);

            foreach ($contacts as $contact) {
                Address::factory()->create([
                    'tenant_id' => $tenant->id,
                    'contact_id' => $contact->id,
                    'password' => $password,
                    'is_main_address' => true,
                ]);
            }
        }
    }
}
