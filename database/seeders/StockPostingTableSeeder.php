<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockPostingTableSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all(['id']);
        $products = Product::all(['id']);

        for ($i = 0; $i < 20; $i++) {
            StockPosting::factory()->create([
                'warehouse_id' => $warehouses->random()?->id,
                'product_id' => $products->random()?->id,
            ]);
        }
    }
}
