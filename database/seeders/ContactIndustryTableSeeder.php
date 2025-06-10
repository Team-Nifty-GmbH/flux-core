<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Contact;
use FluxErp\Models\Industry;
use FluxErp\Models\Pivots\ContactIndustry;
use Illuminate\Database\Seeder;

class ContactIndustryTableSeeder extends Seeder
{
    public function run(): void
    {
        $contactIds = Contact::query()->pluck('id');
        $cutContactIds = $contactIds->random(bcfloor($contactIds->count() * 0.6));

        $industryIds = Industry::query()->pluck('id');
        $cutIndustryIds = $industryIds->random(bcfloor($industryIds->count() * 0.7));

        foreach ($cutContactIds as $contactId) {
            $numGroups = rand(1, floor($cutIndustryIds->count() * 0.5));

            $ids = $cutIndustryIds->random($numGroups);

            foreach ($ids as $id) {
                ContactIndustry::create([
                    'contact_id' => $contactId,
                    'industry_id' => $id,
                ]);
            }
        }
    }
}
