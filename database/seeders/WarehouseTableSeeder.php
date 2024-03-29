<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $addresses = Address::all();
        foreach ($addresses as $address) {
            Warehouse::factory()->create([
                'address_id' => $address->id,
            ]);
        }
    }
}
