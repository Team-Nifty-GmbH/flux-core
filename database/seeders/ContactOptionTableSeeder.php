<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\ContactOption;
use Illuminate\Database\Seeder;

class ContactOptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Address::all() as $address) {
            $address->contactOptions()->saveMany(ContactOption::factory()->count(3)->make());
        }
    }
}
