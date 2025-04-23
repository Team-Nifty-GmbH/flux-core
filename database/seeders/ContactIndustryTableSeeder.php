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
        $contactIds = Contact::query()->get('id');
        $cutContactIds = $contactIds->random(bcfloor($contactIds->count() * 0.6));
        $industryIds = Industry::query()->get('id');
        $cutIndustryIds = $industryIds->random(bcfloor($industryIds->count() * 0.7));

        foreach ($cutContactIds as $cutContactId) {
            $numGroups = rand(1, floor($cutIndustryIds->count() * 0.5));

            $ids = $cutIndustryIds->random($numGroups)->pluck('id')->toArray();

            foreach ($ids as $id) {
                ContactIndustry::factory()->create([
                    'contact_id' => $cutContactId,
                    'industry_id' => $id,
                ]);
            }
        }
    }
}
