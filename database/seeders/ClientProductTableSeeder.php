<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Cart;
use FluxErp\Models\Client;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use Illuminate\Database\Seeder;

class ClientProductTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $productIds = Product::query()->get('id');

        foreach ($clientIds as $clientId) {
            $clientId->products()->attach($productIds->random(rand(3, 9)));
        }
    }
}
