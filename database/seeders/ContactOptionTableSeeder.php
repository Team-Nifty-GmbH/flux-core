<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\ContactOption;
use Illuminate\Database\Seeder;

class ContactOptionTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Address::all(['id']) as $address) {
            $address->contactOptions()->saveMany(ContactOption::factory()->count(3)->make());
        }
    }
}
