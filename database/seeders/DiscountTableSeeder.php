<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Discount;
use FluxErp\Models\OrderPosition;
use Illuminate\Database\Seeder;

class DiscountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $discounts = Discount::all();
        $orderPositions = OrderPosition::all();

        for ($i = 0; $i < 20; $i++) {
            $isNewDiscount = $discounts->isEmpty() ? 1 : rand(0, 1);
            Discount::factory()->create([
                'order_position_id' => $isNewDiscount ? $orderPositions->random()->id : null,
            ]);
        }
    }
}
