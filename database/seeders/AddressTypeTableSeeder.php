<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\AddressType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Seeder;

class AddressTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all(['id']);

        foreach ($tenants as $tenant) {
            $invoiceAddressType = AddressType::query()
                ->where('tenant_id', $tenant->id)
                ->where('address_type_code', 'inv')
                ->first();

            if (! $invoiceAddressType) {
                AddressType::factory()->create([
                    'tenant_id' => $tenant->id,
                    'address_type_code' => 'inv',
                    'name' => 'invoice',
                    'is_locked' => true,
                    'is_unique' => true,
                ]);
            }

            $deliveryAddressType = AddressType::query()
                ->where('tenant_id', $tenant->id)
                ->where('address_type_code', 'del')
                ->first();

            if (! $deliveryAddressType) {
                AddressType::factory()->create([
                    'tenant_id' => $tenant->id,
                    'address_type_code' => 'del',
                    'name' => 'delivery',
                    'is_locked' => true,
                    'is_unique' => true,
                ]);
            }

            AddressType::factory()->count(3)->create([
                'tenant_id' => $tenant->id,
                'address_type_code' => null,
            ]);
        }
    }
}
