<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Cart;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Database\Seeder;

class CartTableSeeder extends Seeder
{
    public function run(): void
    {
        $paymentTypesIds = PaymentType::query()->get('id');
        $priceListIds = PriceList::query()->get('id');

        for ($i = 0; $i < 5; $i++) {
            Cart::factory()->create([
                'payment_type_id' => $paymentTypesIds->random()->getKey(),
                'price_list_id' => $priceListIds->random()->getKey(),
            ]);
        }
    }
}
