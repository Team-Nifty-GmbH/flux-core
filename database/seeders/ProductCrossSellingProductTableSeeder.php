<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Pivots\ProductCrossSellingProduct;
use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use Illuminate\Database\Seeder;

class ProductCrossSellingProductTableSeeder extends Seeder
{
    public function run(): void
    {
        $productCrossSellingIds = ProductCrossSelling::query()->get('id');
        $cutProductCrossSellingIds = $productCrossSellingIds->random(bcfloor($productCrossSellingIds->count() * 0.7));
        $productIds = Product::query()->get('id');
        $cutProductIds = $productIds->random(bcfloor($productIds->count() * 0.7));

        foreach ($cutProductCrossSellingIds as $productCrossSellingId) {
            $productCrossSellingId->products()->attach($cutProductIds->random(
                rand(1, max(1, bcfloor($cutProductIds->count() * 0.3)))
            ));
        }
    }
}
