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
        $orderPositionIds = OrderPosition::query()->pluck('id');
        $cutOrderPositionIds = $orderPositionIds->random(bcfloor($orderPositionIds->count() * 0.3));

        $stockPostingIds = StockPosting::query()->pluck('id');
        $cutStockPostingIds = $stockPostingIds->random(bcfloor($stockPostingIds->count() * 0.6));

        foreach ($cutOrderPositionIds as $orderPositionId) {
            $numGroups = rand(1, floor($cutStockPostingIds->count() * 0.1));

            $selectedStockPostingIds = $cutStockPostingIds->random($numGroups);

            foreach ($selectedStockPostingIds as $stockPostingId) {
                OrderPositionStockPosting::create([
                    'order_position_id' => $orderPositionId,
                    'stock_posting_id' => $stockPostingId,
                    'reserved_amount' => rand(1, 100),
                ]);
            }
        }
    }
}
