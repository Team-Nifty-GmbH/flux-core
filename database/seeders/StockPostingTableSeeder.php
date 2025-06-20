<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockPostingTableSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all('id');
        $products = Product::all('id');
        $orderPositions = OrderPosition::all('id');
        $serialNumbers = SerialNumber::all('id');

        StockPosting::factory()->count(20)->create([
            'warehouse_id' => fn () => $warehouses->random()?->getKey(),
            'product_id' => fn () => $products->random()?->getKey(),
            'order_position_id' => fn () => $orderPositions->random()?->getKey(),
            'serial_number_id' => fn () => $serialNumbers->random()?->getKey(),
        ]);
    }
}
