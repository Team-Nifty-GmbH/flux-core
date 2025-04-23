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
        $warehouseIds = Warehouse::query()->get('id');
        $productIds = Product::query()->get('id');
        $orderPositionIds = OrderPosition::query()->get('id');
        $serialNumberIds = SerialNumber::query()->get('id');

        StockPosting::factory()->count(20)->create([
            'warehouse_id' => fn () => $warehouseIds->random()?->getKey(),
            'product_id' => fn () => $productIds->random()?->getKey(),
            'order_position_id' => fn () => $orderPositionIds->random()?->getKey(),
            'serial_number_id' => fn () => $serialNumberIds->random()?->getKey(),
        ]);
    }
}
