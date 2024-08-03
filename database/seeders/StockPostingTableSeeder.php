<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockPostingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $products = Product::all();

        for ($i = 0; $i < 20; $i++) {
            StockPosting::factory()->create([
                'warehouse_id' => $warehouses->random()?->id,
                'product_id' => $products->random()?->id,
            ]);
        }
    }
}
