<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseTableSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = Address::all(['id']);
        foreach ($addresses as $address) {
            Warehouse::factory()->create([
                'address_id' => $address->id,
            ]);
        }
    }
}
