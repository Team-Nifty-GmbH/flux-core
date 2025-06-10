<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Contact;
use FluxErp\Models\DiscountGroup;
use FluxErp\Models\Pivots\ContactDiscountGroup;
use Illuminate\Database\Seeder;

class ContactDiscountGroupTableSeeder extends Seeder
{
    public function run(): void
    {
        $contactIds = Contact::query()->pluck('id');
        $cutContactIds = $contactIds->random(bcfloor($contactIds->count() * 0.7));

        $discountGroupIds = DiscountGroup::query()->pluck('id');
        $cutDiscountGroupIds = $discountGroupIds->random(bcfloor($discountGroupIds->count() * 0.8));

        foreach ($cutContactIds as $contactId) {
            $numGroups = rand(1, floor($cutDiscountGroupIds->count() * 0.8));

            $selectedDiscountGroupIds = $cutDiscountGroupIds->random($numGroups);

            foreach ($selectedDiscountGroupIds as $discountGroupId) {
                ContactDiscountGroup::create([
                    'contact_id' => $contactId,
                    'discount_group_id' => $discountGroupId,
                ]);
            }
        }
    }
}
