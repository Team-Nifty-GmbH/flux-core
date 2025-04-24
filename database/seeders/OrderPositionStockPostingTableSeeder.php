<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Pivots\OrderPositionStockPosting;
use FluxErp\Models\StockPosting;
use Illuminate\Database\Seeder;

class OrderPositionStockPostingTableSeeder extends Seeder
{
    public function run(): void
    {
        $orderPositionIds = OrderPosition::query()->get('id');
        $cutOrderPositionIds = $orderPositionIds->random(bcfloor($orderPositionIds->count() * 0.3));

        $stockPostingsIds = StockPosting::query()->get('id');
        $cutStockPostingsIds = $stockPostingsIds->random(bcfloor($stockPostingsIds->count() * 0.6));

        foreach ($cutOrderPositionIds as $cutOrderPositionId) {
            $numGroups = rand(1, floor($cutStockPostingsIds->count() * 0.1));

            $ids = $cutStockPostingsIds->random($numGroups)->pluck('id')->toArray();

            foreach ($ids as $id) {
                OrderPositionStockPosting::factory()->create([
                    'order_position_id' => $cutOrderPositionId,
                    'stock_posting_id' => $id,
                ]);
            }
        }
    }
}
