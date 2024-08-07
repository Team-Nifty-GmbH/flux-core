<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use Illuminate\Database\Seeder;

class CountryRegionTableSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::all(['id']);

        foreach ($countries as $country) {
            CountryRegion::factory()->count(3)->create([
                'country_id' => $country->id,
            ]);
        }
    }
}
