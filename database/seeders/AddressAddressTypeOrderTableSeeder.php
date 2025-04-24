<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Order;
use Illuminate\Database\Seeder;

class AddressAddressTypeOrderTableSeeder extends Seeder
{
    public function run(): void
    {
        $orderIds = Order::query()->get('id');
        $cutOrderIds = $orderIds->random(bcfloor($orderIds->count() * 0.6));

        $addressIds = Address::query()->get('id');
        $cutAddressIds = $addressIds->random(bcfloor($addressIds->count() * 0.6));

        $addressTypeIds = AddressType::query()->get('id');
        $cutAddressTypeIds = $addressTypeIds->random(bcfloor($addressTypeIds->count() * 0.6));

        foreach ($cutAddressIds as $addressId) {
            $addressId->addressTypeOrders()->attach($cutOrderIds->random(
                rand(1, bcfloor($cutOrderIds->count() * 0.2))), [
                    'address_type_id' => $cutAddressTypeIds->random()->getKey(),
                ]);
        }
    }
}
