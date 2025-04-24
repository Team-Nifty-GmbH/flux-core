<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\SerialNumber;
use Illuminate\Database\Seeder;

class AddressSerialNumberTableSeeder extends Seeder
{
    public function run(): void
    {
        $addressIds = Address::query()->get('id');
        $cutAddressIds = $addressIds->random(bcfloor($addressIds->count() * 0.5));
        $serialNumberIds = SerialNumber::query()->get('id');
        $cutSerialNumberIds = $serialNumberIds->random(bcfloor($serialNumberIds->count() * 0.75));

        foreach ($cutAddressIds as $addressId) {
            $addressId->serialNumbers()->attach($cutSerialNumberIds->random(
                rand(1, max(1, bcfloor($cutSerialNumberIds->count() * 0.2)))
            ), [
                'quantity' => faker()->numberBetween(1, 10),
            ]);
        }
    }
}
