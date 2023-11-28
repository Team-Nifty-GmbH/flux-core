<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Project;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run(): void
    {
        Project::factory()->count(10)->create();
    }
}
