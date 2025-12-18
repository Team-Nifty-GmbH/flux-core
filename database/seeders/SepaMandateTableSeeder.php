<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use FluxErp\Models\Tenant;
use Illuminate\Database\Seeder;

class SepaMandateTableSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all(['id']);
        $contactBankConnections = ContactBankConnection::all(['id', 'contact_id']);

        foreach ($contactBankConnections as $contactBankConnection) {
            SepaMandate::factory()->count(rand(0, 3))->create([
                'contact_bank_connection_id' => $contactBankConnection->id,
                'contact_id' => $contactBankConnection->contact_id,
                'tenant_id' => $tenants->random()->first()->id,
            ]);
        }
    }
}
