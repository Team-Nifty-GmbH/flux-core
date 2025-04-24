<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Product;
use Illuminate\Database\Seeder;

class AddressProductTableSeeder extends Seeder
{
    public function run(): void
    {
        $addressIds = Address::query()->get('id');
        $cutAddressIds = $addressIds->random(bcfloor($addressIds->count() * 0.6));
        $productIds = Product::query()->get('id');
        $cutProductIds = $productIds->random(bcfloor($productIds->count() * 0.6));

        foreach ($cutAddressIds as $cutAddressId) {
            $cutAddressId->products()->attach($cutProductIds->random(
                rand(1, max(1,bcfloor($cutProductIds->count() * 0.2)))
            ));
        }
    }
}
