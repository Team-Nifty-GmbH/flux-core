<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Discount;
use FluxErp\Models\OrderPosition;
use Illuminate\Database\Seeder;

class DiscountTableSeeder extends Seeder
{
    public function run(): void
    {
        $discounts = Discount::all(['id']);
        $orderPositions = OrderPosition::all(['id']);

        for ($i = 0; $i < 30; $i++) {
            $isNewDiscount = $discounts->isEmpty() ? 1 : rand(0, 1);
            Discount::factory()->create([
                'model_id' => $isNewDiscount ? $orderPositions->random()->id : null,
                'model_type' => morph_alias(OrderPosition::class),
            ]);
        }
    }
}
