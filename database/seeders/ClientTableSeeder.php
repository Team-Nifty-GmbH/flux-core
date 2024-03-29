<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Country;
use Illuminate\Database\Seeder;

class ClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = Country::all();

        for ($i = 0; $i < 3; $i++) {
            Client::factory()->create([
                'country_id' => $countries->random()->id,
            ]);
        }
    }
}
