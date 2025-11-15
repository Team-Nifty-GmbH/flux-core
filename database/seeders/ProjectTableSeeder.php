<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Project;
use FluxErp\Models\Tenant;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all(['id']);

        Project::factory()->count(10)->create([
            'tenant_id' => $tenants->random()->id,
        ]);
    }
}
