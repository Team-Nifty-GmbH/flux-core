<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use FluxErp\Models\Pivots\ContactDiscountGroup;
use Illuminate\Database\Seeder;

class ContactDiscountGroupTableSeeder extends Seeder
{
    public function run(): void
    {
        $contactIds = Discount::query()->get('id');
        $cutContactIds = $contactIds->random(bcfloor($contactIds->count() * 0.7));
        $discountGroupIds = DiscountGroup::query()->get('id');
        $cutDiscountGroupIds = $discountGroupIds->random(bcfloor($discountGroupIds->count() * 0.8));

        foreach ($cutContactIds as $cutContactId) {
            $numGroups = rand(1, floor($cutDiscountGroupIds->count() * 0.8));

            $ids = $cutDiscountGroupIds->random($numGroups)->pluck('id')->toArray();;

            foreach ($ids as $id) {
                ContactDiscountGroup::factory()->create([
                    'contact_id' => $cutContactId,
                    'discount_group_id' => $id,
                ]);
            }
        }
    }
}
