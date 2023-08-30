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
        foreach (Product::all(['id']) as $product) {
            $product->productCrossSellings()->createMany(
                Product::factory()->count(3)->make()->toArray()
            );
        }
    }
}
