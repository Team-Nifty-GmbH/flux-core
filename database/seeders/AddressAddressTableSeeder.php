<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use Illuminate\Database\Seeder;

class AddressAddressTableSeeder extends Seeder
{
    public function run(): void
    {
        $addressIds = Address::query()->get('id');
        $cutAddressIds = $addressIds->random(bcfloor($addressIds->count() * 0.6));
        $addressTypeIds = AddressType::query()->get('id');
        $cutAddressTypeIds = $addressTypeIds->random(bcfloor($addressTypeIds->count() * 0.6));

        foreach ($cutAddressIds as $cutAddressId) {
            $cutAddressId->addressTypes()->attach($cutAddressTypeIds->random(rand(1, 3)));
        }
    }
}
