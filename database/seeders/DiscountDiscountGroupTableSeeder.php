<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use FluxErp\Models\Pivots\DiscountDiscountGroup;
use Illuminate\Database\Seeder;

class DiscountDiscountGroupTableSeeder extends Seeder
{
    public function run(): void
    {
        $discountIds = Discount::query()->get('id');
        $cutDiscountIds = $discountIds->random(bcfloor($discountIds->count() * 0.7));
        $discountGroupIds = DiscountGroup::query()->get('id');
        $cutDiscountGroupIds = $discountGroupIds->random(bcfloor($discountGroupIds->count() * 0.8));

        foreach ($cutDiscountGroupIds as $cutDiscountGroupId) {
            $numGroups = rand(1, floor($cutDiscountIds->count() * 0.8));

            $ids = $cutDiscountIds->random($numGroups)->pluck('id')->toArray();
            foreach ($ids as $id) {
                DiscountDiscountGroup::factory()->create([
                    'discount_id' => $id,
                    'discount_group_id' => $cutDiscountGroupId,
                ]);
            }
        }
    }
}
