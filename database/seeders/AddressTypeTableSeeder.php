<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\AddressType;
use Illuminate\Database\Seeder;

class AddressTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $invoiceAddressType = AddressType::query()
            ->where('address_type_code', 'inv')
            ->first();

        if (! $invoiceAddressType) {
            AddressType::factory()->create([
                'address_type_code' => 'inv',
                'name' => 'invoice',
            ]);
        }

        $deliveryAddressType = AddressType::query()
            ->where('address_type_code', 'del')
            ->first();

        if (! $deliveryAddressType) {
            AddressType::factory()->create([
                'address_type_code' => 'del',
                'name' => 'delivery',
            ]);
        }

        AddressType::factory()->count(3)->create([
            'address_type_code' => null,
        ]);
    }
}
