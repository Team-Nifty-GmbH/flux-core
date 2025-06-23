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
        $discountIds = Discount::query()->pluck('id');
        $cutDiscountIds = $discountIds->random(bcfloor($discountIds->count() * 0.7));

        $discountGroupIds = DiscountGroup::query()->pluck('id');
        $cutDiscountGroupIds = $discountGroupIds->random(bcfloor($discountGroupIds->count() * 0.8));

        foreach ($cutDiscountGroupIds as $discountGroupId) {
            $numGroups = rand(1, floor($cutDiscountIds->count() * 0.8));

            $selectedDiscountIds = $cutDiscountIds->random($numGroups);

            foreach ($selectedDiscountIds as $discountId) {
                DiscountDiscountGroup::create([
                    'discount_id' => $discountId,
                    'discount_group_id' => $discountGroupId,
                ]);
            }
        }
    }
}
