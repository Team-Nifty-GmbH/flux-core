<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Country;
use Illuminate\Database\Seeder;

class ClientTableSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::all(['id']);

        for ($i = 0; $i < 3; $i++) {
            Client::factory()->create([
                'country_id' => $countries->random()->id,
            ]);
        }
    }
}
