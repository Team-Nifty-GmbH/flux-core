<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Contact;
use FluxErp\Models\RecordOrigin;
use FluxErp\Models\Tenant;
use Illuminate\Database\Seeder;

class ContactTableSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all(['id']);
        $recordOrigins = RecordOrigin::query()
            ->where('model_type', morph_alias(Contact::class))
            ->get('id');

        foreach ($tenants as $tenant) {
            Contact::factory()->count(10)->create([
                'tenant_id' => $tenant->id,
                'record_origin_id' => fn () => $recordOrigins->random()->id,
            ]);
        }
    }
}
