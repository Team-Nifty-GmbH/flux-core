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

        for ($i = 0; $i < 10; $i++) {
            ContactDiscount::factory()->create([
                'contact_id' => $cutContactIds->random()->getKey(),
                'discount_id' => $cutDiscountIds->random()->getKey(),
            ]);
        }
    }
}
