<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use Illuminate\Database\Seeder;

class ProductCrossSellingTableSeeder extends Seeder
{
    public function run(): void
    {
        $productIds = Product::query()
            ->select('id')
            ->inRandomOrder()
            ->take(ceil(Product::count() / 2))
            ->get()
            ->toArray();

        foreach (Product::query()->whereIntegerInRaw('id', $productIds)->get(['id']) as $product) {
            $product->productCrossSellings()->createMany(
                ProductCrossSelling::factory()->count(3)->make()->toArray()
            );
        }
    }
}
