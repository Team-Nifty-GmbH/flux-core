<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
use Illuminate\Database\Seeder;

class AddressTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = Client::all();

        foreach ($clients as $client) {
            $invoiceAddressType = AddressType::query()
                ->where('client_id', $client->id)
                ->where('address_type_code', 'inv')
                ->first();

            if (! $invoiceAddressType) {
                AddressType::factory()->create([
                    'client_id' => $client->id,
                    'address_type_code' => 'inv',
                    'name' => 'invoice',
                    'is_locked' => true,
                    'is_unique' => true,
                ]);
            }

            $deliveryAddressType = AddressType::query()
                ->where('client_id', $client->id)
                ->where('address_type_code', 'del')
                ->first();

            if (! $deliveryAddressType) {
                AddressType::factory()->create([
                    'client_id' => $client->id,
                    'address_type_code' => 'del',
                    'name' => 'delivery',
                    'is_locked' => true,
                    'is_unique' => true,
                ]);
            }

            AddressType::factory()->count(3)->create([
                'client_id' => $client->id,
                'address_type_code' => null,
            ]);
        }
    }
}
