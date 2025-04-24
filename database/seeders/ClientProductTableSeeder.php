<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Product;
use Illuminate\Database\Seeder;

class ClientProductTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(bcfloor($clientIds->count() * 0.7));
        $productIds = Product::query()->get('id');
        $cutProductIds = $productIds->random(bcfloor($productIds->count() * 0.7));

        foreach ($cutClientIds as $clientId) {
            $clientId->products()->attach($cutProductIds->random(
                rand(1, max(1, bcfloor($cutProductIds->count() * 0.6)))
            ));
        }
    }
}
