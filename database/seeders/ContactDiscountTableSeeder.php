<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Contact;
use FluxErp\Models\Discount;
use FluxErp\Models\Pivots\ContactDiscount;
use Illuminate\Database\Seeder;

class ContactDiscountTableSeeder extends Seeder
{
    public function run(): void
    {
        $contactIds = Contact::query()->pluck('id');
        $cutContactIds = $contactIds->random(bcfloor($contactIds->count() * 0.7));

        $discountIds = Discount::query()->pluck('id');
        $cutDiscountIds = $discountIds->random(bcfloor($discountIds->count() * 0.7));

        foreach ($cutContactIds as $contactId) {
            $numGroups = rand(1, floor($cutDiscountIds->count() * 0.8));

            $selectedDiscountIds = $cutDiscountIds->random($numGroups);

            foreach ($selectedDiscountIds as $discountId) {
                ContactDiscount::create([
                    'contact_id' => $contactId,
                    'discount_id' => $discountId,
                ]);
            }
        }
    }
}
