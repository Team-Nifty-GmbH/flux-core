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
        $contactIds = Contact::query()->get('id');
        $cutContactIds = $contactIds->random(bcfloor($contactIds->count() * 0.7));
        $discountIds = Discount::query()->get('id');
        $cutDiscountIds = $discountIds->random(bcfloor($discountIds->count() * 0.7));

        foreach ($cutContactIds as $cutContactId) {
            $numGroups = rand(1, floor($cutDiscountIds->count() * 0.8));

            $ids = $cutDiscountIds->random($numGroups)->pluck('id')->toArray();

            foreach ($ids as $id) {
                ContactDiscount::factory()->create([
                    'contact_id' => $cutContactId,
                    'discount_id' => $id,
                ]);
            }
        }
    }
}
