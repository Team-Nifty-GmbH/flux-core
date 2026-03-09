<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Contact;
use FluxErp\Models\RecordOrigin;
use Illuminate\Database\Seeder;

class ContactTableSeeder extends Seeder
{
    public function run(): void
    {
        $recordOrigins = RecordOrigin::query()
            ->where('model_type', morph_alias(Contact::class))
            ->get('id');

        Contact::factory()->count(10)->create([
            'record_origin_id' => fn () => $recordOrigins->random()->id,
        ]);
    }
}
