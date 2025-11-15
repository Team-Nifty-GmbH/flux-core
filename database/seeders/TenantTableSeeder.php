<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Country;
use FluxErp\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantTableSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::all(['id']);

        for ($i = 0; $i < 3; $i++) {
            Tenant::factory()->create([
                'country_id' => $countries->random()->id,
            ]);
        }
    }
}
