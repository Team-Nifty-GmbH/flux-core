<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Cart;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CartTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $modelClass = Arr::random([
                User::class,
                Address::class,
            ]);

            $paymentTypesIds = PaymentType::query()->get('id');
            $cutPaymentTypeIds = $paymentTypesIds->random(bcfloor($paymentTypesIds->count() * 0.6));

            $priceListIds = PriceList::query()->get('id');
            $cutPriceListIds = $priceListIds->random(bcfloor($priceListIds->count() * 0.6));

            $idList = $modelClass::query()->pluck('id')->toArray();
            $instanceId = faker()->unique()->randomElement($idList);

            Cart::factory()->create([
                'payment_type_id' => faker()->boolean(70) ? $cutPaymentTypeIds->random()->getKey() : null,
                'price_list_id' => $cutPriceListIds->random()->getKey(),
                'authenticatable_type' => morph_alias($modelClass),
                'authenticatable_id' => $instanceId,
            ]);
        }
    }
}
