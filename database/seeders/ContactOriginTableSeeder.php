<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\ContactOrigin;
use Illuminate\Database\Seeder;

class ContactOriginTableSeeder extends Seeder
{
    public function run(): void
    {
        ContactOrigin::factory()->count(10)->create();
    }
}
