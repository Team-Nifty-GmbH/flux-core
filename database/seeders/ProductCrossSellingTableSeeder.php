<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Product;
use Illuminate\Database\Seeder;

class ProductCrossSellingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
                Product::factory()->count(3)->make()->toArray()
            );
        }
    }
}
